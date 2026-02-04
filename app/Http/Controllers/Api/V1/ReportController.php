<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get dashboard summary.
     *
     * GET /api/v1/reports/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        // Today's data
        $todayTransactions = Transaction::where('company_id', $user->company_id)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->get();

        // This month's data
        $monthTransactions = Transaction::where('company_id', $user->company_id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->get();

        // Low stock count
        $lowStockCount = Product::active()
            ->whereHas('inventory', function ($q) {
                $q->whereRaw('quantity <= low_stock_threshold');
            })->count();

        // Recent transactions
        $recentTransactions = Transaction::where('company_id', $user->company_id)
            ->with(['user', 'items'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Top products today
        $topProductsToday = TransactionItem::whereHas('transaction', function ($q) use ($user) {
            $q->where('company_id', $user->company_id)
                ->whereDate('created_at', today())
                ->where('status', 'completed');
        })
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return response()->json([
            'data' => [
                'today' => [
                    'sales_total' => (float) $todayTransactions->sum('total'),
                    'transactions_count' => $todayTransactions->count(),
                    'average_sale' => $todayTransactions->count() > 0
                        ? (float) ($todayTransactions->sum('total') / $todayTransactions->count())
                        : 0,
                    'items_sold' => $todayTransactions->sum(fn ($t) => $t->items->sum('quantity')),
                ],
                'this_month' => [
                    'sales_total' => (float) $monthTransactions->sum('total'),
                    'transactions_count' => $monthTransactions->count(),
                    'average_sale' => $monthTransactions->count() > 0
                        ? (float) ($monthTransactions->sum('total') / $monthTransactions->count())
                        : 0,
                ],
                'alerts' => [
                    'low_stock_count' => $lowStockCount,
                ],
                'payment_breakdown_today' => [
                    'cash' => (float) $todayTransactions->where('payment_method', 'cash')->sum('total'),
                    'card' => (float) $todayTransactions->where('payment_method', 'card')->sum('total'),
                    'mobile' => (float) $todayTransactions->where('payment_method', 'mobile')->sum('total'),
                    'bank_transfer' => (float) $todayTransactions->where('payment_method', 'bank_transfer')->sum('total'),
                ],
                'top_products_today' => $topProductsToday->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity_sold' => (int) $item->total_quantity,
                    'total_sales' => (float) $item->total_sales,
                ])->toArray(),
                'recent_transactions' => $recentTransactions->map(fn ($t) => [
                    'id' => $t->id,
                    'transaction_number' => $t->transaction_number,
                    'total' => (float) $t->total,
                    'payment_method' => $t->payment_method,
                    'status' => $t->status,
                    'cashier' => $t->user?->name,
                    'items_count' => $t->items->count(),
                    'created_at' => $t->created_at->toIso8601String(),
                    'created_at_human' => $t->created_at->diffForHumans(),
                ])->toArray(),
            ],
        ]);
    }

    /**
     * Get sales report for a date range.
     *
     * GET /api/v1/reports/sales
     */
    public function sales(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check permission
        if (!$user->isCompanyOwner() && !$user->hasPermission('view_reports')) {
            return response()->json([
                'message' => 'You do not have permission to view reports.',
            ], 403);
        }

        $validated = $request->validate([
            'period' => 'nullable|in:today,week,month,custom',
            'date_from' => 'required_if:period,custom|date',
            'date_to' => 'required_if:period,custom|date|after_or_equal:date_from',
        ]);

        $period = $validated['period'] ?? 'today';

        // Determine date range
        [$startDate, $endDate] = match ($period) {
            'today' => [today(), today()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [$validated['date_from'], $validated['date_to']],
        };

        $transactions = Transaction::where('company_id', $user->company_id)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'completed')
            ->get();

        // Daily breakdown
        $dailyData = $transactions->groupBy(fn ($t) => $t->created_at->format('Y-m-d'))
            ->map(fn ($dayTransactions) => [
                'date' => $dayTransactions->first()->created_at->format('Y-m-d'),
                'sales' => (float) $dayTransactions->sum('total'),
                'transactions' => $dayTransactions->count(),
            ])
            ->values();

        // Top products
        $topProducts = TransactionItem::whereHas('transaction', function ($q) use ($user, $startDate, $endDate) {
            $q->where('company_id', $user->company_id)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('status', 'completed');
        })
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sales')
            ->take(10)
            ->get();

        return response()->json([
            'data' => [
                'period' => [
                    'type' => $period,
                    'start_date' => $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate,
                    'end_date' => $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate,
                ],
                'summary' => [
                    'total_sales' => (float) $transactions->sum('total'),
                    'total_transactions' => $transactions->count(),
                    'average_sale' => $transactions->count() > 0
                        ? (float) ($transactions->sum('total') / $transactions->count())
                        : 0,
                    'total_tax' => (float) $transactions->sum('tax_amount'),
                    'total_discount' => (float) $transactions->sum('discount_amount'),
                ],
                'payment_methods' => [
                    'cash' => [
                        'total' => (float) $transactions->where('payment_method', 'cash')->sum('total'),
                        'count' => $transactions->where('payment_method', 'cash')->count(),
                    ],
                    'card' => [
                        'total' => (float) $transactions->where('payment_method', 'card')->sum('total'),
                        'count' => $transactions->where('payment_method', 'card')->count(),
                    ],
                    'mobile' => [
                        'total' => (float) $transactions->where('payment_method', 'mobile')->sum('total'),
                        'count' => $transactions->where('payment_method', 'mobile')->count(),
                    ],
                    'bank_transfer' => [
                        'total' => (float) $transactions->where('payment_method', 'bank_transfer')->sum('total'),
                        'count' => $transactions->where('payment_method', 'bank_transfer')->count(),
                    ],
                ],
                'daily_breakdown' => $dailyData,
                'top_products' => $topProducts->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity_sold' => (int) $item->total_quantity,
                    'total_sales' => (float) $item->total_sales,
                ])->toArray(),
            ],
        ]);
    }
}
