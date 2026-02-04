<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Get inventory list.
     *
     * GET /api/v1/inventory
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::active()->with(['category', 'inventory']);

        // Filter by low stock
        if ($request->boolean('low_stock')) {
            $query->whereHas('inventory', function ($q) {
                $q->whereRaw('quantity <= low_stock_threshold');
            });
        }

        // Filter by out of stock
        if ($request->boolean('out_of_stock')) {
            $query->whereHas('inventory', function ($q) {
                $q->where('quantity', '<=', 0);
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->get('per_page', 50), 100);
        $products = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $products->map(fn ($product) => $this->formatInventoryItem($product)),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Adjust stock for a product.
     *
     * POST /api/v1/inventory/{product}/adjust
     */
    public function adjust(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        // Check permission
        if (!$user->isCompanyOwner() && !$user->hasPermission('manage_inventory')) {
            return response()->json([
                'message' => 'You do not have permission to adjust inventory.',
            ], 403);
        }

        $product = Product::where('id', $productId)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $validated = $request->validate([
            'type' => 'required|in:received,damaged,returned,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $result = DB::transaction(function () use ($product, $user, $validated) {
                $inventory = $product->inventory;

                if (!$inventory) {
                    // Create inventory record if it doesn't exist
                    $inventory = Inventory::create([
                        'company_id' => $user->company_id,
                        'product_id' => $product->id,
                        'quantity' => 0,
                        'low_stock_threshold' => 10,
                    ]);
                }

                $quantityBefore = $inventory->quantity;

                // Determine quantity change based on type
                $quantityChange = match ($validated['type']) {
                    'received', 'returned' => $validated['quantity'],
                    'damaged' => -$validated['quantity'],
                    'adjustment' => $validated['quantity'], // Can be positive or negative via separate endpoint
                };

                // For adjustment type, check if we need to subtract (when reason contains "decrease" etc)
                // But typically adjustment is a set action, so let's handle it differently
                $quantityAfter = $quantityBefore + $quantityChange;

                // Ensure we don't go negative
                if ($quantityAfter < 0) {
                    throw new \Exception("Cannot reduce stock below 0. Current stock: {$quantityBefore}");
                }

                // Create stock adjustment record
                StockAdjustment::create([
                    'company_id' => $user->company_id,
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => $validated['type'],
                    'quantity_change' => $quantityChange,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'reason' => $validated['reason'] ?? ucfirst($validated['type']),
                ]);

                // Update inventory
                $inventory->update(['quantity' => $quantityAfter]);

                return [
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'quantity_change' => $quantityChange,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully.',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'type' => $validated['type'],
                    'quantity_before' => $result['quantity_before'],
                    'quantity_after' => $result['quantity_after'],
                    'quantity_change' => $result['quantity_change'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get stock adjustment history for a product.
     *
     * GET /api/v1/inventory/{product}/history
     */
    public function history(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $product = Product::where('id', $productId)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $adjustments = StockAdjustment::where('product_id', $productId)
            ->with('user')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'current_stock' => $product->stock_quantity,
            ],
            'data' => $adjustments->map(fn ($adj) => [
                'id' => $adj->id,
                'type' => $adj->type,
                'quantity_change' => $adj->quantity_change,
                'quantity_before' => $adj->quantity_before,
                'quantity_after' => $adj->quantity_after,
                'reason' => $adj->reason,
                'user' => $adj->user?->name,
                'created_at' => $adj->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Get inventory summary.
     *
     * GET /api/v1/inventory/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalProducts = Product::active()->count();

        $lowStockCount = Product::active()
            ->whereHas('inventory', function ($q) {
                $q->whereRaw('quantity <= low_stock_threshold')
                    ->where('quantity', '>', 0);
            })->count();

        $outOfStockCount = Product::active()
            ->whereHas('inventory', function ($q) {
                $q->where('quantity', '<=', 0);
            })->count();

        $totalStockValue = Product::active()
            ->with('inventory')
            ->get()
            ->sum(fn ($p) => ($p->inventory?->quantity ?? 0) * $p->cost_price);

        $totalRetailValue = Product::active()
            ->with('inventory')
            ->get()
            ->sum(fn ($p) => ($p->inventory?->quantity ?? 0) * $p->selling_price);

        return response()->json([
            'data' => [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_stock_value' => (float) $totalStockValue,
                'total_retail_value' => (float) $totalRetailValue,
                'potential_profit' => (float) ($totalRetailValue - $totalStockValue),
            ],
        ]);
    }

    /**
     * Format inventory item for API response.
     */
    protected function formatInventoryItem(Product $product): array
    {
        $inventory = $product->inventory;
        $quantity = $inventory?->quantity ?? 0;
        $threshold = $inventory?->low_stock_threshold ?? 10;

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category' => $product->category?->name,
            'quantity' => $quantity,
            'low_stock_threshold' => $threshold,
            'is_low_stock' => $quantity > 0 && $quantity <= $threshold,
            'is_out_of_stock' => $quantity <= 0,
            'cost_price' => (float) $product->cost_price,
            'selling_price' => (float) $product->selling_price,
            'stock_value' => (float) ($quantity * $product->cost_price),
            'retail_value' => (float) ($quantity * $product->selling_price),
            'last_restocked_at' => $inventory?->last_restocked_at?->toIso8601String(),
        ];
    }
}
