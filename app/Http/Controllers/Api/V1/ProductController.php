<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get list of products.
     *
     * GET /api/v1/pos/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::active()->with(['category', 'inventory']);

        // Search by name, SKU, or barcode
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by barcode (exact match for scanning)
        if ($request->filled('barcode')) {
            $query->where('barcode', $request->barcode);
        }

        // Pagination
        $perPage = min($request->get('per_page', 50), 100);
        $products = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $products->map(fn ($product) => $this->formatProduct($product)),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get a single product by ID or barcode.
     *
     * GET /api/v1/pos/products/{id}
     */
    public function show(Request $request, string $identifier): JsonResponse
    {
        $query = Product::active()->with(['category', 'inventory']);

        // Check if identifier is numeric (ID) or string (barcode)
        if (is_numeric($identifier)) {
            $product = $query->find($identifier);
        } else {
            $product = $query->where('barcode', $identifier)
                ->orWhere('sku', $identifier)
                ->first();
        }

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatProduct($product, true),
        ]);
    }

    /**
     * Get list of categories.
     *
     * GET /api/v1/pos/categories
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = Category::withCount(['products' => function ($query) {
            $query->active();
        }])
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => $this->formatCategory($category));

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Search products by barcode (for scanner).
     *
     * GET /api/v1/pos/products/scan/{barcode}
     */
    public function scanBarcode(string $barcode): JsonResponse
    {
        $product = Product::active()
            ->with(['category', 'inventory'])
            ->where('barcode', $barcode)
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'Product not found with this barcode.',
                'barcode' => $barcode,
            ], 404);
        }

        return response()->json([
            'data' => $this->formatProduct($product),
        ]);
    }

    /**
     * Get low stock products.
     *
     * GET /api/v1/pos/products/low-stock
     */
    public function lowStock(Request $request): JsonResponse
    {
        $products = Product::active()
            ->with(['category', 'inventory'])
            ->whereHas('inventory', function ($query) {
                $query->whereRaw('quantity <= low_stock_threshold');
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($product) => $this->formatProduct($product));

        return response()->json([
            'data' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Format product for API response.
     */
    protected function formatProduct(Product $product, bool $detailed = false): array
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'selling_price' => (float) $product->selling_price,
            'tax_rate' => (float) $product->tax_rate,
            'stock' => $product->stock_quantity,
            'low_stock_threshold' => $product->inventory?->low_stock_threshold ?? 10,
            'is_low_stock' => $product->inventory
                ? $product->inventory->quantity <= $product->inventory->low_stock_threshold
                : false,
            'image_url' => $product->image_path
                ? asset('storage/' . $product->image_path)
                : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
        ];

        if ($detailed) {
            $data['description'] = $product->description;
            $data['cost_price'] = (float) $product->cost_price;
            $data['created_at'] = $product->created_at->toIso8601String();
            $data['updated_at'] = $product->updated_at->toIso8601String();
        }

        return $data;
    }

    /**
     * Format category for API response.
     */
    protected function formatCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'products_count' => $category->products_count ?? 0,
            'parent_id' => $category->parent_id,
        ];
    }
}
