<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    /**
     * Pull data changes since last sync.
     *
     * GET /api/v1/sync/pull
     */
    public function pull(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'since' => 'nullable|date',
            'include' => 'nullable|array',
            'include.*' => 'in:products,categories,inventory',
        ]);

        $since = $validated['since'] ?? null;
        $include = $validated['include'] ?? ['products', 'categories', 'inventory'];

        $data = [];
        $syncTimestamp = now()->toIso8601String();

        // Products
        if (in_array('products', $include)) {
            $productsQuery = Product::with(['category', 'inventory']);

            if ($since) {
                $productsQuery->where('updated_at', '>', $since);
            }

            $data['products'] = $productsQuery->get()->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'cost_price' => (float) $product->cost_price,
                'selling_price' => (float) $product->selling_price,
                'tax_rate' => (float) $product->tax_rate,
                'stock' => $product->inventory?->quantity ?? 0,
                'low_stock_threshold' => $product->inventory?->low_stock_threshold ?? 10,
                'is_active' => $product->is_active,
                'image_url' => $product->image_path ? asset('storage/' . $product->image_path) : null,
                'updated_at' => $product->updated_at->toIso8601String(),
            ])->toArray();
        }

        // Categories
        if (in_array('categories', $include)) {
            $categoriesQuery = Category::withCount(['products' => fn ($q) => $q->active()]);

            if ($since) {
                $categoriesQuery->where('updated_at', '>', $since);
            }

            $data['categories'] = $categoriesQuery->get()->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'parent_id' => $category->parent_id,
                'products_count' => $category->products_count,
                'updated_at' => $category->updated_at->toIso8601String(),
            ])->toArray();
        }

        // Get deleted product IDs if syncing incrementally
        if ($since) {
            $data['deleted_products'] = DB::table('products')
                ->where('company_id', $user->company_id)
                ->where('is_active', false)
                ->where('updated_at', '>', $since)
                ->pluck('id')
                ->toArray();
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'synced_at' => $syncTimestamp,
                'since' => $since,
                'is_full_sync' => is_null($since),
            ],
        ]);
    }

    /**
     * Push offline transactions to server.
     *
     * POST /api/v1/sync/push
     */
    public function push(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'transactions' => 'required|array|min:1|max:50',
            'transactions.*.offline_id' => 'required|string|max:100',
            'transactions.*.items' => 'required|array|min:1',
            'transactions.*.items.*.product_id' => 'required|exists:products,id',
            'transactions.*.items.*.quantity' => 'required|integer|min:1',
            'transactions.*.payment_method' => 'required|in:cash,card,mobile,bank_transfer',
            'transactions.*.amount_paid' => 'required|numeric|min:0',
            'transactions.*.customer_name' => 'nullable|string|max:255',
            'transactions.*.customer_phone' => 'nullable|string|max:50',
            'transactions.*.discount_amount' => 'nullable|numeric|min:0',
            'transactions.*.notes' => 'nullable|string|max:500',
            'transactions.*.created_at' => 'nullable|date', // Offline timestamp
        ]);

        $results = [];
        $syncedCount = 0;
        $failedCount = 0;

        foreach ($validated['transactions'] as $transactionData) {
            try {
                $result = $this->processOfflineTransaction($user, $transactionData);
                $results[] = [
                    'offline_id' => $transactionData['offline_id'],
                    'success' => true,
                    'server_id' => $result['id'],
                    'transaction_number' => $result['transaction_number'],
                ];
                $syncedCount++;
            } catch (\Exception $e) {
                $results[] = [
                    'offline_id' => $transactionData['offline_id'],
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
                $failedCount++;
            }
        }

        return response()->json([
            'data' => [
                'results' => $results,
                'synced_count' => $syncedCount,
                'failed_count' => $failedCount,
            ],
            'meta' => [
                'synced_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Process a single offline transaction.
     */
    protected function processOfflineTransaction($user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $subtotal = 0;
            $taxAmount = 0;
            $items = [];

            // Process items
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];

                // Check stock - but be lenient for offline sync
                // Log a warning if stock would go negative
                $currentStock = $product->stock_quantity;

                $itemSubtotal = $product->selling_price * $quantity;
                $itemTax = $itemSubtotal * ($product->tax_rate / 100);

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'cost_price' => $product->cost_price ?? 0,
                    'tax_rate' => $product->tax_rate,
                    'tax_amount' => $itemTax,
                    'subtotal' => $itemSubtotal + $itemTax,
                    'stock_warning' => $currentStock < $quantity,
                ];

                $subtotal += $itemSubtotal;
                $taxAmount += $itemTax;
            }

            $discountAmount = $data['discount_amount'] ?? 0;
            $total = $subtotal + $taxAmount - $discountAmount;
            $amountPaid = $data['amount_paid'];
            $changeGiven = max(0, $amountPaid - $total);

            // Create transaction
            $transaction = Transaction::create([
                'company_id' => $user->company_id,
                'branch_id' => $user->currentBranch()?->id,
                'transaction_number' => Transaction::generateTransactionNumber(),
                'user_id' => $user->id,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'amount_paid' => $amountPaid,
                'change_given' => $changeGiven,
                'status' => 'completed',
                'notes' => ($data['notes'] ?? '') . ' [Synced from offline: ' . $data['offline_id'] . ']',
                'created_at' => $data['created_at'] ?? now(),
            ]);

            // Create items and update inventory
            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $item['tax_amount'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Update inventory
                $inventory = $item['product']->inventory;
                if ($inventory) {
                    $quantityBefore = $inventory->quantity;
                    $quantityAfter = max(0, $quantityBefore - $item['quantity']); // Don't go negative

                    StockAdjustment::create([
                        'company_id' => $user->company_id,
                        'product_id' => $item['product']->id,
                        'user_id' => $user->id,
                        'type' => 'sold',
                        'quantity_change' => -$item['quantity'],
                        'quantity_before' => $quantityBefore,
                        'quantity_after' => $quantityAfter,
                        'reason' => 'Sale (offline sync): ' . $transaction->transaction_number,
                    ]);

                    $inventory->update(['quantity' => $quantityAfter]);
                }
            }

            return [
                'id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
            ];
        });
    }

    /**
     * Get sync status and last sync info.
     *
     * GET /api/v1/sync/status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get counts for sync overview
        $productsCount = Product::active()->count();
        $categoriesCount = Category::count();

        // Get last modified timestamps
        $lastProductUpdate = Product::max('updated_at');
        $lastCategoryUpdate = Category::max('updated_at');

        return response()->json([
            'data' => [
                'products_count' => $productsCount,
                'categories_count' => $categoriesCount,
                'last_product_update' => $lastProductUpdate,
                'last_category_update' => $lastCategoryUpdate,
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }
}
