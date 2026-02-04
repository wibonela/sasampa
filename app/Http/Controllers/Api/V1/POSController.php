<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    /**
     * Process a checkout/sale.
     *
     * POST /api/v1/pos/checkout
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,mobile,bank_transfer',
            'amount_paid' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_tin' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'offline_id' => 'nullable|string|max:100', // For offline sync
        ]);

        try {
            $transaction = DB::transaction(function () use ($validated, $request) {
                $subtotal = 0;
                $taxAmount = 0;
                $items = [];

                // Process each item
                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $quantity = $item['quantity'];

                    // Check stock
                    if ($product->stock_quantity < $quantity) {
                        throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock_quantity}");
                    }

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
                    ];

                    $subtotal += $itemSubtotal;
                    $taxAmount += $itemTax;
                }

                $discountAmount = $validated['discount_amount'] ?? 0;
                $total = $subtotal + $taxAmount - $discountAmount;
                $amountPaid = $validated['amount_paid'];
                $changeGiven = max(0, $amountPaid - $total);

                // Note: No payment validation - user can accept any amount
                // (allows flexibility for verbal discounts, negotiations, etc.)

                // Create transaction
                $user = $request->user();
                $transaction = Transaction::create([
                    'company_id' => $user->company_id,
                    'branch_id' => $user->currentBranch()?->id,
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'user_id' => $user->id,
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'customer_tin' => $validated['customer_tin'] ?? null,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total' => $total,
                    'payment_method' => $validated['payment_method'],
                    'amount_paid' => $amountPaid,
                    'change_given' => $changeGiven,
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Create transaction items and update inventory
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
                        $quantityAfter = $quantityBefore - $item['quantity'];

                        StockAdjustment::create([
                            'company_id' => $user->company_id,
                            'product_id' => $item['product']->id,
                            'user_id' => $user->id,
                            'type' => 'sold',
                            'quantity_change' => -$item['quantity'],
                            'quantity_before' => $quantityBefore,
                            'quantity_after' => $quantityAfter,
                            'reason' => 'Sale: ' . $transaction->transaction_number,
                        ]);

                        $inventory->update(['quantity' => $quantityAfter]);
                    }
                }

                return $transaction;
            });

            $transaction->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully.',
                'data' => $this->formatTransaction($transaction),
                'offline_id' => $validated['offline_id'] ?? null,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'offline_id' => $validated['offline_id'] ?? null,
            ], 422);
        }
    }

    /**
     * Void a transaction.
     *
     * POST /api/v1/pos/transactions/{id}/void
     */
    public function voidTransaction(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Check permission
        if (!$user->isCompanyOwner() && !$user->hasPermission('void_transactions')) {
            return response()->json([
                'message' => 'You do not have permission to void transactions.',
            ], 403);
        }

        $transaction = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found.',
            ], 404);
        }

        if ($transaction->status === 'voided') {
            return response()->json([
                'message' => 'Transaction is already voided.',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($transaction, $user, $validated) {
                // Restore inventory for each item
                foreach ($transaction->items as $item) {
                    $inventory = $item->product?->inventory;
                    if ($inventory) {
                        $quantityBefore = $inventory->quantity;
                        $quantityAfter = $quantityBefore + $item->quantity;

                        StockAdjustment::create([
                            'company_id' => $user->company_id,
                            'product_id' => $item->product_id,
                            'user_id' => $user->id,
                            'type' => 'returned',
                            'quantity_change' => $item->quantity,
                            'quantity_before' => $quantityBefore,
                            'quantity_after' => $quantityAfter,
                            'reason' => 'Voided sale: ' . $transaction->transaction_number . '. Reason: ' . $validated['reason'],
                        ]);

                        $inventory->update(['quantity' => $quantityAfter]);
                    }
                }

                // Update transaction status
                $transaction->update([
                    'status' => 'voided',
                    'notes' => ($transaction->notes ? $transaction->notes . "\n" : '') .
                        "Voided by {$user->name}: {$validated['reason']}",
                ]);
            });

            $transaction->refresh();
            $transaction->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Transaction voided successfully.',
                'data' => $this->formatTransaction($transaction),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get receipt data for a transaction.
     *
     * GET /api/v1/pos/transactions/{id}/receipt
     */
    public function receipt(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $transaction = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->with(['items.product', 'user', 'branch'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatReceipt($transaction),
        ]);
    }

    /**
     * Format transaction for API response.
     */
    protected function formatTransaction(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'status' => $transaction->status,
            'subtotal' => (float) $transaction->subtotal,
            'tax_amount' => (float) $transaction->tax_amount,
            'discount_amount' => (float) $transaction->discount_amount,
            'total' => (float) $transaction->total,
            'payment_method' => $transaction->payment_method,
            'amount_paid' => (float) $transaction->amount_paid,
            'change_given' => (float) $transaction->change_given,
            'customer_name' => $transaction->customer_name,
            'customer_phone' => $transaction->customer_phone,
            'customer_tin' => $transaction->customer_tin,
            'notes' => $transaction->notes,
            'items' => $transaction->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'tax_rate' => (float) $item->tax_rate,
                'tax_amount' => (float) $item->tax_amount,
                'subtotal' => (float) $item->subtotal,
            ])->toArray(),
            'cashier' => [
                'id' => $transaction->user_id,
                'name' => $transaction->user?->name,
            ],
            'branch' => $transaction->branch ? [
                'id' => $transaction->branch->id,
                'name' => $transaction->branch->name,
            ] : null,
            'created_at' => $transaction->created_at->toIso8601String(),
        ];
    }

    /**
     * Format receipt data for printing.
     */
    protected function formatReceipt(Transaction $transaction): array
    {
        $company = $transaction->user?->company;

        return [
            'company' => [
                'name' => $company?->name ?? 'Company',
                'address' => $company?->address,
                'phone' => $company?->phone,
                'email' => $company?->email,
                'logo' => $company?->logo ? asset('storage/' . $company->logo) : null,
            ],
            'branch' => $transaction->branch ? [
                'name' => $transaction->branch->name,
                'address' => $transaction->branch->address,
                'phone' => $transaction->branch->phone,
            ] : null,
            'transaction' => [
                'number' => $transaction->transaction_number,
                'date' => $transaction->created_at->format('d/m/Y'),
                'time' => $transaction->created_at->format('H:i'),
                'cashier' => $transaction->user?->name,
                'status' => $transaction->status,
            ],
            'customer' => [
                'name' => $transaction->customer_name,
                'phone' => $transaction->customer_phone,
                'tin' => $transaction->customer_tin,
            ],
            'items' => $transaction->items->map(fn ($item) => [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) ($item->unit_price * $item->quantity),
                'tax' => (float) $item->tax_amount,
            ])->toArray(),
            'totals' => [
                'subtotal' => (float) $transaction->subtotal,
                'tax' => (float) $transaction->tax_amount,
                'discount' => (float) $transaction->discount_amount,
                'total' => (float) $transaction->total,
            ],
            'payment' => [
                'method' => $transaction->payment_method,
                'method_label' => ucfirst(str_replace('_', ' ', $transaction->payment_method)),
                'amount_paid' => (float) $transaction->amount_paid,
                'change' => (float) $transaction->change_given,
            ],
            'footer' => [
                'message' => 'Thank you for your purchase!',
            ],
        ];
    }
}
