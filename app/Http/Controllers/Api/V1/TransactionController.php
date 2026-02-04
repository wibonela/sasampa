<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Get list of transactions.
     *
     * GET /api/v1/pos/transactions
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Transaction::where('company_id', $user->company_id)
            ->with(['user', 'branch', 'items']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by cashier
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search by transaction number or customer
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $transactions = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $transactions->map(fn ($t) => $this->formatTransaction($t)),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Get a single transaction.
     *
     * GET /api/v1/pos/transactions/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $transaction = Transaction::where('id', $id)
            ->where('company_id', $user->company_id)
            ->with(['user', 'branch', 'items.product'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatTransaction($transaction, true),
        ]);
    }

    /**
     * Get transactions for today.
     *
     * GET /api/v1/pos/transactions/today
     */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = Transaction::where('company_id', $user->company_id)
            ->whereDate('created_at', today())
            ->with(['user', 'items'])
            ->orderByDesc('created_at')
            ->get();

        $completedTransactions = $transactions->where('status', 'completed');

        return response()->json([
            'data' => $transactions->map(fn ($t) => $this->formatTransaction($t)),
            'summary' => [
                'total_transactions' => $transactions->count(),
                'completed_transactions' => $completedTransactions->count(),
                'voided_transactions' => $transactions->where('status', 'voided')->count(),
                'total_sales' => (float) $completedTransactions->sum('total'),
                'total_tax' => (float) $completedTransactions->sum('tax_amount'),
                'total_discount' => (float) $completedTransactions->sum('discount_amount'),
                'payment_methods' => [
                    'cash' => (float) $completedTransactions->where('payment_method', 'cash')->sum('total'),
                    'card' => (float) $completedTransactions->where('payment_method', 'card')->sum('total'),
                    'mobile' => (float) $completedTransactions->where('payment_method', 'mobile')->sum('total'),
                    'bank_transfer' => (float) $completedTransactions->where('payment_method', 'bank_transfer')->sum('total'),
                ],
            ],
        ]);
    }

    /**
     * Get my transactions (for current user).
     *
     * GET /api/v1/pos/transactions/mine
     */
    public function mine(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Transaction::where('user_id', $user->id)
            ->with(['branch', 'items']);

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            // Default to today
            $query->whereDate('created_at', today());
        }

        $transactions = $query->orderByDesc('created_at')->get();
        $completedTransactions = $transactions->where('status', 'completed');

        return response()->json([
            'data' => $transactions->map(fn ($t) => $this->formatTransaction($t)),
            'summary' => [
                'total_transactions' => $transactions->count(),
                'completed_transactions' => $completedTransactions->count(),
                'total_sales' => (float) $completedTransactions->sum('total'),
            ],
        ]);
    }

    /**
     * Format transaction for API response.
     */
    protected function formatTransaction(Transaction $transaction, bool $detailed = false): array
    {
        $data = [
            'id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'status' => $transaction->status,
            'total' => (float) $transaction->total,
            'payment_method' => $transaction->payment_method,
            'customer_name' => $transaction->customer_name,
            'items_count' => $transaction->items->count(),
            'cashier' => $transaction->user?->name,
            'branch' => $transaction->branch?->name,
            'created_at' => $transaction->created_at->toIso8601String(),
            'created_at_human' => $transaction->created_at->diffForHumans(),
        ];

        if ($detailed) {
            $data['subtotal'] = (float) $transaction->subtotal;
            $data['tax_amount'] = (float) $transaction->tax_amount;
            $data['discount_amount'] = (float) $transaction->discount_amount;
            $data['amount_paid'] = (float) $transaction->amount_paid;
            $data['change_given'] = (float) $transaction->change_given;
            $data['customer_phone'] = $transaction->customer_phone;
            $data['customer_tin'] = $transaction->customer_tin;
            $data['notes'] = $transaction->notes;
            $data['items'] = $transaction->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'tax_rate' => (float) $item->tax_rate,
                'tax_amount' => (float) $item->tax_amount,
                'subtotal' => (float) $item->subtotal,
                'product' => $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'image_url' => $item->product->image_path
                        ? asset('storage/' . $item->product->image_path)
                        : null,
                ] : null,
            ])->toArray();
            $data['branch_details'] = $transaction->branch ? [
                'id' => $transaction->branch->id,
                'name' => $transaction->branch->name,
                'code' => $transaction->branch->code,
            ] : null;
        }

        return $data;
    }
}
