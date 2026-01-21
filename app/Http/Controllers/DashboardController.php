<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        // Platform admins should use the admin dashboard
        if ($user->isPlatformAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $companyId = $user->company_id;

        // Company owners see all branches, staff see only their current branch
        $branchId = $user->isCompanyOwner() ? null : $user->currentBranch()?->id;

        // Get cached dashboard stats
        $stats = $this->cacheService->dashboardStats($companyId, $branchId);

        // Get cached recent transactions
        $recentTransactionsData = $this->cacheService->recentTransactions($companyId, $branchId);

        // Get total products count (lightweight query)
        $totalProducts = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        // Transform data for view compatibility
        $todaySales = $stats['today']['sales'];
        $todayTransactions = $stats['today']['transactions'];
        $lowStockCount = $stats['low_stock_count'];

        // === PROFIT METRICS ===
        // Today's profit calculation
        $todayTransactionIds = Transaction::completed()
            ->whereDate('created_at', today())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->pluck('id');

        $todayCogs = TransactionItem::whereIn('transaction_id', $todayTransactionIds)
            ->selectRaw('SUM(cost_price * quantity) as total')
            ->value('total') ?? 0;

        $todayGrossProfit = $todaySales - $todayCogs;

        $todayExpenses = Expense::whereDate('expense_date', today())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('SUM(amount * quantity) as total')
            ->value('total') ?? 0;

        $todayNetProfit = $todayGrossProfit - $todayExpenses;

        // This Month metrics
        $monthStart = now()->startOfMonth();
        $monthTransactions = Transaction::completed()
            ->where('created_at', '>=', $monthStart)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        $monthSales = (clone $monthTransactions)->sum('total');
        $monthTransactionCount = (clone $monthTransactions)->count();

        $monthTransactionIds = (clone $monthTransactions)->pluck('id');
        $monthCogs = TransactionItem::whereIn('transaction_id', $monthTransactionIds)
            ->selectRaw('SUM(cost_price * quantity) as total')
            ->value('total') ?? 0;

        $monthGrossProfit = $monthSales - $monthCogs;

        $monthExpenses = Expense::where('expense_date', '>=', $monthStart)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('SUM(amount * quantity) as total')
            ->value('total') ?? 0;

        $monthNetProfit = $monthGrossProfit - $monthExpenses;
        $monthProfitMargin = $monthSales > 0 ? ($monthNetProfit / $monthSales) * 100 : 0;

        // Yesterday comparison
        $yesterdaySales = Transaction::completed()
            ->whereDate('created_at', now()->subDay())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total');

        $salesGrowth = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;

        // Last month comparison
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $lastMonthSales = Transaction::completed()
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total');

        $monthSalesGrowth = $lastMonthSales > 0 ? (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        // Top selling products today
        $topProducts = TransactionItem::whereIn('transaction_id', $todayTransactionIds)
            ->selectRaw('product_name, SUM(quantity) as qty_sold, SUM(subtotal) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Convert to collections for view
        $recentTransactions = collect($recentTransactionsData)->map(function ($item) {
            return (object) [
                'id' => $item['id'],
                'transaction_number' => $item['transaction_number'],
                'total' => $item['total'],
                'status' => $item['status'],
                'created_at' => Carbon::parse($item['created_at']),
                'user' => (object) ($item['user'] ?? ['name' => 'Unknown']),
            ];
        });

        return view('dashboard.index', compact(
            'todaySales',
            'todayTransactions',
            'todayGrossProfit',
            'todayExpenses',
            'todayNetProfit',
            'totalProducts',
            'lowStockCount',
            'monthSales',
            'monthTransactionCount',
            'monthGrossProfit',
            'monthExpenses',
            'monthNetProfit',
            'monthProfitMargin',
            'salesGrowth',
            'monthSalesGrowth',
            'recentTransactions',
            'topProducts'
        ));
    }
}
