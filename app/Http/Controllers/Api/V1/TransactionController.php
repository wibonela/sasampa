<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->sales()
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
            ->sales()
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
            ->sales()
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
     * Get transaction summary (today, week, month).
     *
     * GET /api/v1/pos/transactions/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $baseQuery = Transaction::where('company_id', $user->company_id)
            ->sales()
            ->where('status', 'completed');

        $todayQuery = (clone $baseQuery)->whereDate('created_at', today());
        $weekQuery = (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        $monthQuery = (clone $baseQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);

        return response()->json([
            'data' => [
                'today_total' => (float) $todayQuery->sum('total'),
                'today_count' => $todayQuery->count(),
                'week_total' => (float) $weekQuery->sum('total'),
                'week_count' => $weekQuery->count(),
                'month_total' => (float) $monthQuery->sum('total'),
                'month_count' => $monthQuery->count(),
            ],
        ]);
    }

    /**
     * Get rich business intelligence insights.
     *
     * GET /api/v1/pos/transactions/insights
     */
    public function insights(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $baseQuery = Transaction::where('company_id', $companyId)
            ->sales()
            ->where('status', 'completed');

        // ---------------------------------------------------------------
        // 1. Period comparison (today vs yesterday, week vs week, month vs month)
        // ---------------------------------------------------------------
        $todayTotal = (float) (clone $baseQuery)->whereDate('created_at', today())->sum('total');
        $todayCount = (clone $baseQuery)->whereDate('created_at', today())->count();

        $yesterdayTotal = (float) (clone $baseQuery)->whereDate('created_at', today()->subDay())->sum('total');
        $yesterdayCount = (clone $baseQuery)->whereDate('created_at', today()->subDay())->count();

        $thisWeekTotal = (float) (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
        $thisWeekCount = (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $lastWeekTotal = (float) (clone $baseQuery)->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('total');
        $lastWeekCount = (clone $baseQuery)->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();

        $thisMonthTotal = (float) (clone $baseQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total');
        $thisMonthCount = (clone $baseQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $lastMonthTotal = (float) (clone $baseQuery)->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('total');
        $lastMonthCount = (clone $baseQuery)->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count();

        $periodComparison = [
            'today' => [
                'total' => $todayTotal,
                'count' => $todayCount,
                'previous_total' => $yesterdayTotal,
                'previous_count' => $yesterdayCount,
                'total_change_pct' => $yesterdayTotal > 0 ? round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 1) : ($todayTotal > 0 ? 100.0 : 0.0),
                'count_change_pct' => $yesterdayCount > 0 ? round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100, 1) : ($todayCount > 0 ? 100.0 : 0.0),
            ],
            'this_week' => [
                'total' => $thisWeekTotal,
                'count' => $thisWeekCount,
                'previous_total' => $lastWeekTotal,
                'previous_count' => $lastWeekCount,
                'total_change_pct' => $lastWeekTotal > 0 ? round((($thisWeekTotal - $lastWeekTotal) / $lastWeekTotal) * 100, 1) : ($thisWeekTotal > 0 ? 100.0 : 0.0),
                'count_change_pct' => $lastWeekCount > 0 ? round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1) : ($thisWeekCount > 0 ? 100.0 : 0.0),
            ],
            'this_month' => [
                'total' => $thisMonthTotal,
                'count' => $thisMonthCount,
                'previous_total' => $lastMonthTotal,
                'previous_count' => $lastMonthCount,
                'total_change_pct' => $lastMonthTotal > 0 ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1) : ($thisMonthTotal > 0 ? 100.0 : 0.0),
                'count_change_pct' => $lastMonthCount > 0 ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) : ($thisMonthCount > 0 ? 100.0 : 0.0),
            ],
        ];

        // ---------------------------------------------------------------
        // 2. Average transaction value
        // ---------------------------------------------------------------
        $avgTransaction = [
            'today' => $todayCount > 0 ? round($todayTotal / $todayCount, 2) : 0.0,
            'this_week' => $thisWeekCount > 0 ? round($thisWeekTotal / $thisWeekCount, 2) : 0.0,
            'this_month' => $thisMonthCount > 0 ? round($thisMonthTotal / $thisMonthCount, 2) : 0.0,
        ];

        // ---------------------------------------------------------------
        // 3. Top 5 selling products this month (by quantity and by revenue)
        // ---------------------------------------------------------------
        $monthTransactionIds = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->pluck('id');

        $topByQuantity = TransactionItem::whereIn('transaction_id', $monthTransactionIds)
            ->where('company_id', $companyId)
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'total_quantity' => (int) $item->total_quantity,
                'total_revenue' => (float) $item->total_revenue,
            ]);

        $topByRevenue = TransactionItem::whereIn('transaction_id', $monthTransactionIds)
            ->where('company_id', $companyId)
            ->select('product_id', 'product_name', DB::raw('SUM(subtotal) as total_revenue'), DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'total_revenue' => (float) $item->total_revenue,
                'total_quantity' => (int) $item->total_quantity,
            ]);

        // ---------------------------------------------------------------
        // 4. Payment method breakdown this month
        // ---------------------------------------------------------------
        $paymentBreakdown = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();

        $paymentTotal = $paymentBreakdown->sum('total');
        $paymentMethods = $paymentBreakdown->map(fn ($row) => [
            'method' => $row->payment_method,
            'count' => (int) $row->count,
            'total' => (float) $row->total,
            'percentage' => $paymentTotal > 0 ? round(((float) $row->total / $paymentTotal) * 100, 1) : 0.0,
        ])->values();

        // ---------------------------------------------------------------
        // 5. Discount stats this month
        // ---------------------------------------------------------------
        $monthDiscounted = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('discount_amount', '>', 0);

        $totalDiscounts = (float) (clone $monthDiscounted)->sum('discount_amount');
        $discountedCount = (clone $monthDiscounted)->count();

        $discountStats = [
            'total_discounts' => $totalDiscounts,
            'discounted_sales_count' => $discountedCount,
            'avg_discount_per_sale' => $discountedCount > 0 ? round($totalDiscounts / $discountedCount, 2) : 0.0,
            'total_sales_this_month' => $thisMonthCount,
            'discount_rate_pct' => $thisMonthCount > 0 ? round(($discountedCount / $thisMonthCount) * 100, 1) : 0.0,
        ];

        // ---------------------------------------------------------------
        // 6. Peak hours today (sales count & total per hour)
        // ---------------------------------------------------------------
        $peakHours = (clone $baseQuery)
            ->whereDate('created_at', today())
            ->select(
                DB::raw("CAST(strftime('%H', created_at) AS INTEGER) as hour"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('hour')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'hour' => (int) $row->hour,
                'label' => sprintf('%02d:00 - %02d:59', $row->hour, $row->hour),
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ]);

        // ---------------------------------------------------------------
        // 7. Customer stats this month
        // ---------------------------------------------------------------
        $monthCompleted = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $totalCustomerSales = (clone $monthCompleted)->count();
        $registeredCustomerSales = (clone $monthCompleted)->whereNotNull('customer_id')->count();
        $walkInSales = $totalCustomerSales - $registeredCustomerSales;

        $topCustomers = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->whereNotNull('customer_id')
            ->select(
                'customer_id',
                DB::raw('MAX(customer_name) as customer_name'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total) as total_spent')
            )
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit(3)
            ->get()
            ->map(fn ($row) => [
                'customer_id' => $row->customer_id,
                'customer_name' => $row->customer_name,
                'transaction_count' => (int) $row->transaction_count,
                'total_spent' => (float) $row->total_spent,
            ]);

        $customerStats = [
            'total_sales' => $totalCustomerSales,
            'registered_customer_sales' => $registeredCustomerSales,
            'walk_in_sales' => $walkInSales,
            'returning_customer_pct' => $totalCustomerSales > 0 ? round(($registeredCustomerSales / $totalCustomerSales) * 100, 1) : 0.0,
            'top_customers' => $topCustomers,
        ];

        // ---------------------------------------------------------------
        // 8. Low-margin alerts (products sold where unit_price <= cost_price)
        // ---------------------------------------------------------------
        $lowMarginAlerts = TransactionItem::whereIn('transaction_id', $monthTransactionIds)
            ->where('company_id', $companyId)
            ->whereNotNull('cost_price')
            ->where('cost_price', '>', 0)
            ->whereColumn('unit_price', '<=', 'cost_price')
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('AVG(unit_price) as avg_selling_price'),
                DB::raw('AVG(cost_price) as avg_cost_price'),
                DB::raw('SUM((unit_price - cost_price) * quantity) as total_loss')
            )
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_loss')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'total_quantity_sold' => (int) $item->total_quantity,
                'avg_selling_price' => round((float) $item->avg_selling_price, 2),
                'avg_cost_price' => round((float) $item->avg_cost_price, 2),
                'total_loss' => round((float) $item->total_loss, 2),
            ]);

        // ---------------------------------------------------------------
        // Build response
        // ---------------------------------------------------------------
        return response()->json([
            'data' => [
                'period_comparison' => $periodComparison,
                'average_transaction_value' => $avgTransaction,
                'top_products' => [
                    'by_quantity' => $topByQuantity,
                    'by_revenue' => $topByRevenue,
                ],
                'payment_methods' => $paymentMethods,
                'discount_stats' => $discountStats,
                'peak_hours' => $peakHours,
                'customer_stats' => $customerStats,
                'low_margin_alerts' => $lowMarginAlerts,
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
            'type' => $transaction->type ?? 'sale',
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
