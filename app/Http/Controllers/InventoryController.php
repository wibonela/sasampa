<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Inventory::with('product.category');

        if ($request->filled('search')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->search($request->search);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'low') {
                $query->lowStock();
            } elseif ($request->status === 'out') {
                $query->where('quantity', 0);
            }
        }

        $inventory = $query->orderBy('quantity', 'asc')->paginate(20);
        $lowStockCount = Inventory::lowStock()->count();
        $outOfStockCount = Inventory::where('quantity', 0)->count();

        return view('inventory.index', compact('inventory', 'lowStockCount', 'outOfStockCount'));
    }

    public function adjust(Product $product): View
    {
        $product->load('inventory');
        return view('inventory.adjust', compact('product'));
    }

    public function storeAdjustment(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:received,damaged,adjustment,returned',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $product) {
            $inventory = $product->inventory ?? Inventory::create([
                'product_id' => $product->id,
                'quantity' => 0,
            ]);

            $quantityBefore = $inventory->quantity;
            $quantityChange = in_array($validated['type'], ['received', 'returned'])
                ? $validated['quantity']
                : -$validated['quantity'];
            $quantityAfter = max(0, $quantityBefore + $quantityChange);

            StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $validated['type'],
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $validated['reason'],
            ]);

            $inventory->update([
                'quantity' => $quantityAfter,
                'last_restocked_at' => in_array($validated['type'], ['received']) ? now() : $inventory->last_restocked_at,
            ]);
        });

        return redirect()->route('products.show', $product)
            ->with('success', 'Stock adjusted successfully.');
    }

    public function history(Request $request): View
    {
        $query = StockAdjustment::with(['product', 'user']);

        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $adjustments = $query->latest()->paginate(50);
        $products = Product::orderBy('name')->get();

        return view('inventory.history', compact('adjustments', 'products'));
    }
}
