<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::with('user')->sales();

        if ($request->filled('search')) {
            $query->where('transaction_number', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(25);

        // Build insights data
        $insights = $this->getInsights();

        return view('transactions.index', compact('transactions', 'insights'));
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load('items.product', 'user');
        return view('transactions.show', compact('transaction'));
    }

    public function void(Transaction $transaction): RedirectResponse
    {
        if ($transaction->status !== 'completed') {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'This transaction cannot be voided.');
        }

        $transaction->update(['status' => 'voided']);

        // Restore inventory
        foreach ($transaction->items as $item) {
            if ($item->product && $item->product->inventory) {
                $item->product->inventory->increment('quantity', $item->quantity);
            }
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction voided successfully. Stock has been restored.');
    }

    private function getInsights(): array
    {
        $baseQuery = Transaction::sales()->where('status', 'completed');

        // Period comparison
        $todayTotal = (float) (clone $baseQuery)->whereDate('created_at', today())->sum('total');
        $todayCount = (clone $baseQuery)->whereDate('created_at', today())->count();
        $yesterdayTotal = (float) (clone $baseQuery)->whereDate('created_at', today()->subDay())->sum('total');
        $yesterdayCount = (clone $baseQuery)->whereDate('created_at', today()->subDay())->count();

        $thisWeekTotal = (float) (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
        $thisWeekCount = (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $lastWeekTotal = (float) (clone $baseQuery)->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('total');

        $thisMonthTotal = (float) (clone $baseQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total');
        $thisMonthCount = (clone $baseQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $lastMonthTotal = (float) (clone $baseQuery)->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('total');

        $changePct = fn ($current, $previous) => $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : ($current > 0 ? 100.0 : 0.0);

        // Top products this month
        $monthTxIds = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->pluck('id');

        $companyId = auth()->user()->company_id;

        $topProducts = TransactionItem::whereIn('transaction_id', $monthTxIds)
            ->where('company_id', $companyId)
            ->select('product_name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Payment breakdown this month
        $paymentBreakdown = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();
        $paymentTotal = $paymentBreakdown->sum('total');

        // Discount stats
        $monthDiscounted = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('discount_amount', '>', 0);
        $totalDiscounts = (float) (clone $monthDiscounted)->sum('discount_amount');
        $discountedCount = (clone $monthDiscounted)->count();

        // Peak hours today
        $peakHours = (clone $baseQuery)
            ->whereDate('created_at', today())
            ->select(
                DB::raw("CAST(strftime('%H', created_at) AS INTEGER) as hour"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('hour')
            ->orderByDesc('count')
            ->get();

        // Customer stats
        $monthCompleted = (clone $baseQuery)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        $totalCustomerSales = (clone $monthCompleted)->count();
        $registeredCustomerSales = (clone $monthCompleted)->whereNotNull('customer_id')->count();

        $topCustomers = (clone $baseQuery)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('MAX(customer_name) as customer_name'), DB::raw('COUNT(*) as transaction_count'), DB::raw('SUM(total) as total_spent'))
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit(3)
            ->get();

        // Low margin alerts
        $lowMarginAlerts = TransactionItem::whereIn('transaction_id', $monthTxIds)
            ->where('company_id', $companyId)
            ->whereNotNull('cost_price')
            ->where('cost_price', '>', 0)
            ->whereColumn('unit_price', '<=', 'cost_price')
            ->select('product_name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('AVG(unit_price) as avg_price'), DB::raw('AVG(cost_price) as avg_cost'), DB::raw('SUM((unit_price - cost_price) * quantity) as total_loss'))
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_loss')
            ->limit(10)
            ->get();

        return [
            'today_total' => $todayTotal,
            'today_count' => $todayCount,
            'yesterday_total' => $yesterdayTotal,
            'today_change' => $changePct($todayTotal, $yesterdayTotal),
            'week_total' => $thisWeekTotal,
            'week_count' => $thisWeekCount,
            'week_change' => $changePct($thisWeekTotal, $lastWeekTotal),
            'month_total' => $thisMonthTotal,
            'month_count' => $thisMonthCount,
            'month_change' => $changePct($thisMonthTotal, $lastMonthTotal),
            'avg_today' => $todayCount > 0 ? round($todayTotal / $todayCount) : 0,
            'avg_month' => $thisMonthCount > 0 ? round($thisMonthTotal / $thisMonthCount) : 0,
            'top_products' => $topProducts,
            'payment_breakdown' => $paymentBreakdown,
            'payment_total' => $paymentTotal,
            'total_discounts' => $totalDiscounts,
            'discounted_count' => $discountedCount,
            'discount_rate' => $thisMonthCount > 0 ? round(($discountedCount / $thisMonthCount) * 100, 1) : 0,
            'peak_hours' => $peakHours,
            'total_customer_sales' => $totalCustomerSales,
            'registered_sales' => $registeredCustomerSales,
            'walk_in_sales' => $totalCustomerSales - $registeredCustomerSales,
            'top_customers' => $topCustomers,
            'low_margin_alerts' => $lowMarginAlerts,
        ];
    }
}
