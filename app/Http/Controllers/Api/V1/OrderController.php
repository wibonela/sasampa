<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Transaction::where('company_id', $user->company_id)
            ->where('type', 'order')
            ->with(['user', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->get('per_page', 20), 100);
        $orders = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $orders->map(fn ($t) => $this->formatOrder($t)),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_tin' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'valid_days' => 'nullable|integer|min:1|max:90',
        ]);

        try {
            $order = DB::transaction(function () use ($validated, $request) {
                $subtotal = 0;
                $taxAmount = 0;
                $items = [];

                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $quantity = $item['quantity'];

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
                $validDays = $validated['valid_days'] ?? 7;

                $user = $request->user();

                // Resolve customer info
                $customerId = $validated['customer_id'] ?? null;
                $customerName = $validated['customer_name'];
                $customerPhone = $validated['customer_phone'] ?? null;
                $customerTin = $validated['customer_tin'] ?? null;

                if ($customerId) {
                    $customer = Customer::findOrFail($customerId);
                    $customerName = $customerName ?: $customer->name;
                    $customerPhone = $customerPhone ?: $customer->phone;
                    $customerTin = $customerTin ?: $customer->tin;
                }

                $order = Transaction::create([
                    'company_id' => $user->company_id,
                    'branch_id' => $user->currentBranch()?->id,
                    'customer_id' => $customerId,
                    'transaction_number' => Transaction::generateOrderNumber(),
                    'type' => 'order',
                    'user_id' => $user->id,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'customer_tin' => $customerTin,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total' => $total,
                    'payment_method' => null,
                    'amount_paid' => 0,
                    'change_given' => 0,
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                    'valid_until' => now()->addDays($validDays),
                ]);

                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $order->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'cost_price' => $item['cost_price'],
                        'tax_rate' => $item['tax_rate'],
                        'tax_amount' => $item['tax_amount'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                return $order;
            });

            $order->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Order saved successfully.',
                'data' => $this->formatOrder($order, true),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('type', 'order')
            ->with(['user', 'branch', 'items.product'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'data' => $this->formatOrder($order, true),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('type', 'order')
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pending order not found.'], 404);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_tin' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($order, $validated) {
                // Delete old items
                $order->items()->delete();

                $subtotal = 0;
                $taxAmount = 0;

                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $quantity = $item['quantity'];

                    $itemSubtotal = $product->selling_price * $quantity;
                    $itemTax = $itemSubtotal * ($product->tax_rate / 100);

                    TransactionItem::create([
                        'transaction_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $product->selling_price,
                        'cost_price' => $product->cost_price ?? 0,
                        'tax_rate' => $product->tax_rate,
                        'tax_amount' => $itemTax,
                        'subtotal' => $itemSubtotal + $itemTax,
                    ]);

                    $subtotal += $itemSubtotal;
                    $taxAmount += $itemTax;
                }

                $discountAmount = $validated['discount_amount'] ?? 0;
                $total = $subtotal + $taxAmount - $discountAmount;

                // Resolve customer info
                $customerId = $validated['customer_id'] ?? null;
                $customerName = $validated['customer_name'];
                $customerPhone = $validated['customer_phone'] ?? null;
                $customerTin = $validated['customer_tin'] ?? null;

                if ($customerId) {
                    $customer = Customer::findOrFail($customerId);
                    $customerName = $customerName ?: $customer->name;
                    $customerPhone = $customerPhone ?: $customer->phone;
                    $customerTin = $customerTin ?: $customer->tin;
                }

                $order->update([
                    'customer_id' => $customerId,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'customer_tin' => $customerTin,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total' => $total,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });

            $order->refresh();
            $order->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully.',
                'data' => $this->formatOrder($order, true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function convertToSale(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('type', 'order')
            ->where('status', 'pending')
            ->with('items')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pending order not found.'], 404);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,mobile,bank_transfer',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($order, $validated, $user) {
                // Check stock for all items
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if (!$product) {
                        throw new \Exception("Product '{$item->product_name}' no longer exists.");
                    }
                    if ($product->stock_quantity < $item->quantity) {
                        throw new \Exception("Insufficient stock for {$item->product_name}. Available: {$product->stock_quantity}");
                    }
                }

                // Deduct inventory
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    $inventory = $product->inventory;
                    if ($inventory) {
                        $quantityBefore = $inventory->quantity;
                        $quantityAfter = $quantityBefore - $item->quantity;

                        StockAdjustment::create([
                            'company_id' => $user->company_id,
                            'product_id' => $product->id,
                            'user_id' => $user->id,
                            'type' => 'sold',
                            'quantity_change' => -$item->quantity,
                            'quantity_before' => $quantityBefore,
                            'quantity_after' => $quantityAfter,
                            'reason' => 'Order converted to sale: ' . $order->transaction_number,
                        ]);

                        $inventory->update(['quantity' => $quantityAfter]);
                    }
                }

                $changeGiven = max(0, $validated['amount_paid'] - $order->total);

                $order->update([
                    'type' => 'sale',
                    'status' => 'completed',
                    'payment_method' => $validated['payment_method'],
                    'amount_paid' => $validated['amount_paid'],
                    'change_given' => $changeGiven,
                ]);
            });

            $order->refresh();
            $order->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Order converted to sale successfully.',
                'data' => $this->formatOrder($order, true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('type', 'order')
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pending order not found.'], 404);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $reason = $validated['reason'] ?? '';
        $notes = $order->notes
            ? $order->notes . "\nCancelled by {$user->name}: {$reason}"
            : "Cancelled by {$user->name}: {$reason}";

        $order->update([
            'status' => 'cancelled',
            'notes' => $notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled.',
            'data' => $this->formatOrder($order),
        ]);
    }

    public function proforma(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('type', 'order')
            ->with(['items.product', 'user', 'branch'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $company = $order->user?->company;

        return response()->json([
            'data' => [
                'company' => [
                    'name' => $company?->name ?? 'Company',
                    'address' => $company?->address,
                    'phone' => $company?->phone,
                    'email' => $company?->email,
                    'logo' => $company?->logo ? asset('storage/' . $company->logo) : null,
                ],
                'branch' => $order->branch ? [
                    'name' => $order->branch->name,
                    'address' => $order->branch->address,
                    'phone' => $order->branch->phone,
                ] : null,
                'order' => [
                    'number' => $order->transaction_number,
                    'date' => $order->created_at->format('d/m/Y'),
                    'time' => $order->created_at->format('H:i'),
                    'valid_until' => $order->valid_until?->format('d/m/Y'),
                    'cashier' => $order->user?->name,
                    'status' => $order->status,
                ],
                'customer' => [
                    'name' => $order->customer_name,
                    'phone' => $order->customer_phone,
                    'tin' => $order->customer_tin,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) ($item->unit_price * $item->quantity),
                    'tax' => (float) $item->tax_amount,
                ])->toArray(),
                'totals' => [
                    'subtotal' => (float) $order->subtotal,
                    'tax' => (float) $order->tax_amount,
                    'discount' => (float) $order->discount_amount,
                    'total' => (float) $order->total,
                ],
                'notes' => $order->notes,
            ],
        ]);
    }

    protected function formatOrder(Transaction $order, bool $detailed = false): array
    {
        $data = [
            'id' => $order->id,
            'transaction_number' => $order->transaction_number,
            'type' => $order->type,
            'status' => $order->status,
            'total' => (float) $order->total,
            'payment_method' => $order->payment_method,
            'customer_name' => $order->customer_name,
            'items_count' => $order->items->count(),
            'cashier' => $order->user?->name,
            'branch' => $order->branch?->name,
            'valid_until' => $order->valid_until?->toIso8601String(),
            'created_at' => $order->created_at->toIso8601String(),
            'created_at_human' => $order->created_at->diffForHumans(),
        ];

        if ($detailed) {
            $data['subtotal'] = (float) $order->subtotal;
            $data['tax_amount'] = (float) $order->tax_amount;
            $data['discount_amount'] = (float) $order->discount_amount;
            $data['amount_paid'] = (float) $order->amount_paid;
            $data['change_given'] = (float) $order->change_given;
            $data['customer_phone'] = $order->customer_phone;
            $data['customer_tin'] = $order->customer_tin;
            $data['notes'] = $order->notes;
            $data['items'] = $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'tax_rate' => (float) $item->tax_rate,
                'tax_amount' => (float) $item->tax_amount,
                'subtotal' => (float) $item->subtotal,
            ])->toArray();
        }

        return $data;
    }
}
