<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class POSController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        $products = Product::active()
            ->with(['category', 'inventory'])
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function getProducts(Request $request): JsonResponse
    {
        $query = Product::active()->with(['category', 'inventory']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->orderBy('name')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'selling_price' => $product->selling_price,
                'tax_rate' => $product->tax_rate,
                'stock' => $product->stock_quantity,
                'image_url' => $product->image_path ? asset('storage/' . $product->image_path) : null,
                'category' => $product->category?->name,
            ];
        });

        return response()->json($products);
    }

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
        ]);

        try {
            $transaction = DB::transaction(function () use ($validated) {
                $subtotal = 0;
                $taxAmount = 0;
                $items = [];

                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $quantity = $item['quantity'];

                    // Check stock
                    if ($product->stock_quantity < $quantity) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }

                    $itemSubtotal = $product->selling_price * $quantity;
                    $itemTax = $itemSubtotal * ($product->tax_rate / 100);

                    $items[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => $product->selling_price,
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

                // Create transaction
                $transaction = Transaction::create([
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'user_id' => Auth::id(),
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
                            'product_id' => $item['product']->id,
                            'user_id' => Auth::id(),
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

            return response()->json([
                'success' => true,
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'total' => $transaction->total,
                    'change_given' => $transaction->change_given,
                ],
                'receipt_url' => route('pos.receipt', $transaction),
                'pdf_url' => route('pos.receipt.pdf', $transaction),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt(Transaction $transaction): View
    {
        $transaction->load('items.product', 'user');
        return view('pos.receipt', compact('transaction'));
    }

    public function receiptPdf(Transaction $transaction): Response
    {
        $transaction->load('items.product', 'user');

        // Calculate dynamic height based on number of items
        // Base height (header, footer, totals) ~150mm + ~20mm per item
        $baseHeight = 150;
        $itemHeight = 20;
        $totalHeight = $baseHeight + ($transaction->items->count() * $itemHeight);

        // Convert mm to points (1mm = 2.83465 points)
        $widthPoints = 80 * 2.83465;  // 80mm width
        $heightPoints = $totalHeight * 2.83465;

        $pdf = Pdf::loadView('pos.receipt-pdf', compact('transaction'));
        $pdf->setPaper([0, 0, $widthPoints, $heightPoints], 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('receipt-' . $transaction->transaction_number . '.pdf');
    }
}
