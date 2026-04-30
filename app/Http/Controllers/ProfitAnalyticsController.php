<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\ProfitBreakdownService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfitAnalyticsController extends Controller
{
    public function __construct(private ProfitBreakdownService $profit)
    {
    }

    public function index(Request $request): View
    {
        $period = $request->input('period', 'month');
        $branchId = $request->input('branch') ? (int) $request->input('branch') : null;
        $includeExpenses = $request->boolean('include_expenses', true);
        $expenseCategoryIds = array_map('intval', (array) $request->input('expense_category_ids', []));

        $window = $this->profit->resolvePeriod($period, [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);
        /** @var Carbon $from */ $from = $window['from'];
        /** @var Carbon $to */ $to = $window['to'];
        /** @var Carbon $previousFrom */ $previousFrom = $window['previous_from'];
        /** @var Carbon $previousTo */ $previousTo = $window['previous_to'];

        $dateFrom = $from->toDateString();
        $dateTo = $to->toDateString();

        $branches = Branch::active()->orderBy('name')->get();
        $expenseCategories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        // Current + previous period summaries via service
        $current = $this->profit->summary($from, $to, $branchId, $includeExpenses, $expenseCategoryIds);
        $previous = $this->profit->summary($previousFrom, $previousTo, $branchId, $includeExpenses, $expenseCategoryIds);

        $totalRevenue = $current['revenue'];
        $cogs = $current['cogs'];
        $grossProfit = $current['gross_profit'];
        $grossMargin = $current['gross_margin'];
        $operatingExpenses = $current['operating_expenses'];
        $netProfit = $current['net_profit'];
        $netMargin = $current['net_margin'];
        $transactionCount = $current['transactions_count'];

        $expenseCount = Expense::overlappingPeriod($from, $to)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(!empty($expenseCategoryIds), fn ($q) => $q->whereIn('expense_category_id', $expenseCategoryIds))
            ->count();

        $avgTransactionValue = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;

        $prevRevenue = $previous['revenue'];
        $prevGrossProfit = $previous['gross_profit'];
        $prevExpenses = $previous['operating_expenses'];
        $prevNetProfit = $previous['net_profit'];

        $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : ($totalRevenue > 0 ? 100 : 0);
        $grossProfitGrowth = $prevGrossProfit != 0 ? (($grossProfit - $prevGrossProfit) / abs($prevGrossProfit)) * 100 : ($grossProfit > 0 ? 100 : 0);
        $expenseGrowth = $prevExpenses > 0 ? (($operatingExpenses - $prevExpenses) / $prevExpenses) * 100 : ($operatingExpenses > 0 ? 100 : 0);
        $netProfitGrowth = $prevNetProfit != 0 ? (($netProfit - $prevNetProfit) / abs($prevNetProfit)) * 100 : ($netProfit > 0 ? 100 : ($netProfit < 0 ? -100 : 0));

        // Trend data (daily, hourly when period=today). Map service keys to
        // legacy view shape so existing template keeps working.
        $trendRaw = $this->profit->trend($from, $to, $branchId, $includeExpenses, $expenseCategoryIds, $period === 'today');
        $trendData = array_map(fn ($t) => [
            'period' => $t['bucket'],
            'sales' => $t['revenue'],
            'cogs' => $t['cogs'],
            'expenses' => $t['expenses'],
            'profit' => $includeExpenses ? $t['net_profit'] : $t['gross_profit'],
        ], $trendRaw);

        // Expense breakdown by category
        $topExpenseCategories = collect($this->profit->expensesByCategory($from, $to, $branchId, $expenseCategoryIds))
            ->take(5)
            ->map(fn ($c) => (object) ['category' => $c['name'], 'total' => $c['amount']]);

        $paymentBreakdown = $this->getPaymentBreakdown($dateFrom, $dateTo, $branchId);

        $branchComparison = null;
        if (!$branchId && $branches->count() > 1) {
            $branchComparison = collect($this->profit->branchComparison($from, $to, $includeExpenses, $expenseCategoryIds))
                ->map(fn ($b) => [
                    'name' => $b['name'],
                    'sales' => $b['revenue'],
                    'cogs' => $b['revenue'] - $b['gross_profit'],
                    'profit' => $b['net_profit'],
                ]);
        }

        $performanceData = $this->getPerformanceData($dateFrom, $dateTo, $branchId);

        $topProductsByProfit = $this->profit->topProductsByProfit($from, $to, $branchId, 10);

        return view('analytics.profit.index', compact(
            'branches',
            'branchId',
            'period',
            'dateFrom',
            'dateTo',
            'includeExpenses',
            'expenseCategories',
            'expenseCategoryIds',
            'totalRevenue',
            'cogs',
            'grossProfit',
            'grossMargin',
            'operatingExpenses',
            'netProfit',
            'netMargin',
            'transactionCount',
            'expenseCount',
            'avgTransactionValue',
            'revenueGrowth',
            'grossProfitGrowth',
            'expenseGrowth',
            'netProfitGrowth',
            'prevRevenue',
            'prevGrossProfit',
            'prevExpenses',
            'prevNetProfit',
            'trendData',
            'topExpenseCategories',
            'paymentBreakdown',
            'branchComparison',
            'performanceData',
            'topProductsByProfit'
        ));
    }

    public function byBranch(Request $request): View
    {
        $period = $request->input('period', 'month');
        $dateRange = $this->getDateRange($period, $request);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $branches = Branch::active()->orderBy('name')->get();

        $branchData = $branches->map(function ($branch) use ($dateFrom, $dateTo) {
            $salesQuery = Transaction::completed()->sales()
                ->where('branch_id', $branch->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

            $sales = (clone $salesQuery)->sum('total');
            $transactions = (clone $salesQuery)->count();

            $transactionIds = (clone $salesQuery)->pluck('id');
            $cogs = TransactionItem::whereIn('transaction_id', $transactionIds)
                ->selectRaw('SUM(cost_price * quantity) as total')
                ->value('total') ?? 0;

            $profit = $sales - $cogs;
            $margin = $sales > 0 ? ($profit / $sales) * 100 : 0;

            return [
                'branch' => $branch,
                'sales' => $sales,
                'cogs' => $cogs,
                'profit' => $profit,
                'margin' => $margin,
                'transactions' => $transactions,
                'avg_transaction' => $transactions > 0 ? $sales / $transactions : 0,
            ];
        })->sortByDesc('profit')->values();

        // Calculate totals
        $totalSales = $branchData->sum('sales');
        $totalCogs = $branchData->sum('cogs');
        $totalProfit = $branchData->sum('profit');
        $totalTransactions = $branchData->sum('transactions');

        // Best and worst performers
        $bestBranch = $branchData->first();
        $worstBranch = $branchData->last();

        return view('analytics.profit.by-branch', compact(
            'branches',
            'branchData',
            'period',
            'dateFrom',
            'dateTo',
            'totalSales',
            'totalCogs',
            'totalProfit',
            'totalTransactions',
            'bestBranch',
            'worstBranch'
        ));
    }

    public function trends(Request $request): View
    {
        $period = $request->input('period', 'year');
        $branchId = $request->input('branch');

        $branches = Branch::active()->orderBy('name')->get();

        // Get data for the selected period
        $dateRange = $this->getDateRange($period, $request);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Monthly trends for the year
        $monthlyTrends = $this->getMonthlyTrends($branchId);

        // Weekly trends for current month
        $weeklyTrends = $this->getWeeklyTrends($branchId);

        // Year over year comparison
        $yearComparison = $this->getYearComparison($branchId);

        // Calculate summary statistics
        $avgMonthlyProfit = $monthlyTrends->avg('profit');
        $bestMonth = $monthlyTrends->sortByDesc('profit')->first();
        $worstMonth = $monthlyTrends->sortBy('profit')->first();

        return view('analytics.profit.trends', compact(
            'branches',
            'branchId',
            'period',
            'dateFrom',
            'dateTo',
            'monthlyTrends',
            'weeklyTrends',
            'yearComparison',
            'avgMonthlyProfit',
            'bestMonth',
            'worstMonth'
        ));
    }

    private function getDateRange(string $period, Request $request): array
    {
        $customFrom = $request->input('date_from');
        $customTo = $request->input('date_to');

        if ($customFrom && $customTo) {
            $from = Carbon::parse($customFrom);
            $to = Carbon::parse($customTo);
            $diff = $from->diffInDays($to);

            return [
                'from' => $customFrom,
                'to' => $customTo,
                'previous_from' => $from->copy()->subDays($diff + 1)->format('Y-m-d'),
                'previous_to' => $from->copy()->subDay()->format('Y-m-d'),
            ];
        }

        return match ($period) {
            'today' => [
                'from' => now()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
                'previous_from' => now()->subDay()->format('Y-m-d'),
                'previous_to' => now()->subDay()->format('Y-m-d'),
            ],
            'week' => [
                'from' => now()->startOfWeek()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
                'previous_from' => now()->subWeek()->startOfWeek()->format('Y-m-d'),
                'previous_to' => now()->subWeek()->endOfWeek()->format('Y-m-d'),
            ],
            'month' => [
                'from' => now()->startOfMonth()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
                'previous_from' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                'previous_to' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'quarter' => [
                'from' => now()->startOfQuarter()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
                'previous_from' => now()->subQuarter()->startOfQuarter()->format('Y-m-d'),
                'previous_to' => now()->subQuarter()->endOfQuarter()->format('Y-m-d'),
            ],
            'year' => [
                'from' => now()->startOfYear()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
                'previous_from' => now()->subYear()->startOfYear()->format('Y-m-d'),
                'previous_to' => now()->subYear()->endOfYear()->format('Y-m-d'),
            ],
            default => [
                'from' => now()->startOfMonth()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
                'previous_from' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                'previous_to' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
        };
    }

    /**
     * Build a COGS query grouped by period using a join.
     */
    private function buildCogsQuery(string $dateFrom, string $dateTo, ?int $branchId = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->where(fn ($q) => $q->where('transactions.type', 'sale')->orWhereNull('transactions.type'))
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        if ($branchId) {
            $query->where('transactions.branch_id', $branchId);
        }

        return $query;
    }

    private function getPaymentBreakdown(string $dateFrom, string $dateTo, ?int $branchId): \Illuminate\Support\Collection
    {
        $query = Transaction::completed()->sales()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    private function getPerformanceData(string $dateFrom, string $dateTo, ?int $branchId): array
    {
        $salesQuery = Transaction::completed()->sales()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }

        $dailySales = (clone $salesQuery)
            ->selectRaw('DATE(created_at) as date, SUM(total) as amount')
            ->groupBy('date')
            ->pluck('amount', 'date')
            ->toArray();

        $dailyCogs = $this->buildCogsQuery($dateFrom, $dateTo, $branchId)
            ->selectRaw('DATE(transactions.created_at) as date, SUM(transaction_items.cost_price * transaction_items.quantity) as amount')
            ->groupBy('date')
            ->pluck('amount', 'date')
            ->toArray();

        $allDates = array_unique(array_merge(array_keys($dailySales), array_keys($dailyCogs)));

        $dailyProfit = collect($allDates)->map(function ($date) use ($dailySales, $dailyCogs) {
            $sales = $dailySales[$date] ?? 0;
            $cogs = $dailyCogs[$date] ?? 0;
            return [
                'date' => $date,
                'sales' => $sales,
                'cogs' => $cogs,
                'profit' => $sales - $cogs,
            ];
        });

        $bestDay = $dailyProfit->sortByDesc('profit')->first();
        $worstDay = $dailyProfit->sortBy('profit')->first();
        $avgDailyProfit = $dailyProfit->avg('profit');

        return [
            'best_day' => $bestDay,
            'worst_day' => $worstDay,
            'avg_daily_profit' => $avgDailyProfit,
            'profitable_days' => $dailyProfit->where('profit', '>', 0)->count(),
            'loss_days' => $dailyProfit->where('profit', '<', 0)->count(),
            'total_days' => $dailyProfit->count(),
        ];
    }

    private function getMonthlyTrends(?int $branchId): \Illuminate\Support\Collection
    {
        $months = collect();

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $from = $date->copy()->startOfMonth()->format('Y-m-d');
            $to = $date->copy()->endOfMonth()->format('Y-m-d');

            $salesQuery = Transaction::completed()->sales()
                ->whereBetween('created_at', [$from, $to . ' 23:59:59']);

            if ($branchId) {
                $salesQuery->where('branch_id', $branchId);
            }

            $sales = (clone $salesQuery)->sum('total');

            $transactionIds = (clone $salesQuery)->pluck('id');
            $cogs = TransactionItem::whereIn('transaction_id', $transactionIds)
                ->selectRaw('SUM(cost_price * quantity) as total')
                ->value('total') ?? 0;

            $months->push([
                'month' => $date->format('M Y'),
                'month_short' => $date->format('M'),
                'sales' => $sales,
                'cogs' => $cogs,
                'profit' => $sales - $cogs,
            ]);
        }

        return $months;
    }

    private function getWeeklyTrends(?int $branchId): \Illuminate\Support\Collection
    {
        $weeks = collect();

        for ($i = 3; $i >= 0; $i--) {
            $date = now()->subWeeks($i);
            $from = $date->copy()->startOfWeek()->format('Y-m-d');
            $to = $date->copy()->endOfWeek()->format('Y-m-d');

            $salesQuery = Transaction::completed()->sales()
                ->whereBetween('created_at', [$from, $to . ' 23:59:59']);

            if ($branchId) {
                $salesQuery->where('branch_id', $branchId);
            }

            $sales = (clone $salesQuery)->sum('total');

            $transactionIds = (clone $salesQuery)->pluck('id');
            $cogs = TransactionItem::whereIn('transaction_id', $transactionIds)
                ->selectRaw('SUM(cost_price * quantity) as total')
                ->value('total') ?? 0;

            $weeks->push([
                'week' => 'Week ' . $date->weekOfMonth,
                'period' => $from . ' - ' . $to,
                'sales' => $sales,
                'cogs' => $cogs,
                'profit' => $sales - $cogs,
            ]);
        }

        return $weeks;
    }

    private function getYearComparison(?int $branchId): array
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        $currentYearSalesQuery = Transaction::completed()->sales()
            ->whereYear('created_at', $currentYear);
        $lastYearSalesQuery = Transaction::completed()->sales()
            ->whereYear('created_at', $lastYear);

        if ($branchId) {
            $currentYearSalesQuery->where('branch_id', $branchId);
            $lastYearSalesQuery->where('branch_id', $branchId);
        }

        $currentSales = (clone $currentYearSalesQuery)->sum('total');
        $currentTransactionIds = (clone $currentYearSalesQuery)->pluck('id');
        $currentCogs = TransactionItem::whereIn('transaction_id', $currentTransactionIds)
            ->selectRaw('SUM(cost_price * quantity) as total')
            ->value('total') ?? 0;

        $lastSales = (clone $lastYearSalesQuery)->sum('total');
        $lastTransactionIds = (clone $lastYearSalesQuery)->pluck('id');
        $lastCogs = TransactionItem::whereIn('transaction_id', $lastTransactionIds)
            ->selectRaw('SUM(cost_price * quantity) as total')
            ->value('total') ?? 0;

        return [
            'current' => [
                'year' => $currentYear,
                'sales' => $currentSales,
                'cogs' => $currentCogs,
                'profit' => $currentSales - $currentCogs,
            ],
            'last' => [
                'year' => $lastYear,
                'sales' => $lastSales,
                'cogs' => $lastCogs,
                'profit' => $lastSales - $lastCogs,
            ],
            'growth' => $lastSales > 0 ? (($currentSales - $lastSales) / $lastSales) * 100 : 0,
        ];
    }
}
