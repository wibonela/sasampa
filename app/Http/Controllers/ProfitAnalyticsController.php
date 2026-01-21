<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfitAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->input('period', 'month');
        $branchId = $request->input('branch');

        // Calculate date range based on period
        $dateRange = $this->getDateRange($period, $request);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];
        $previousFrom = $dateRange['previous_from'];
        $previousTo = $dateRange['previous_to'];

        // Get branches for filter
        $branches = Branch::active()->orderBy('name')->get();

        // Build queries with optional branch filter
        $salesQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        $expenseQuery = Expense::whereBetween('expense_date', [$dateFrom, $dateTo]);

        $prevSalesQuery = Transaction::completed()
            ->whereBetween('created_at', [$previousFrom, $previousTo . ' 23:59:59']);
        $prevExpenseQuery = Expense::whereBetween('expense_date', [$previousFrom, $previousTo]);

        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
            $expenseQuery->where('branch_id', $branchId);
            $prevSalesQuery->where('branch_id', $branchId);
            $prevExpenseQuery->where('branch_id', $branchId);
        }

        // Current period metrics
        $totalSales = (clone $salesQuery)->sum('total');
        $totalExpenses = (clone $expenseQuery)->selectRaw('SUM(amount * quantity) as total')->value('total') ?? 0;
        $netProfit = $totalSales - $totalExpenses;
        $profitMargin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;
        $transactionCount = (clone $salesQuery)->count();
        $expenseCount = (clone $expenseQuery)->count();
        $avgTransactionValue = $transactionCount > 0 ? $totalSales / $transactionCount : 0;

        // Previous period metrics for comparison
        $prevSales = (clone $prevSalesQuery)->sum('total');
        $prevExpenses = (clone $prevExpenseQuery)->selectRaw('SUM(amount * quantity) as total')->value('total') ?? 0;
        $prevProfit = $prevSales - $prevExpenses;

        // Calculate growth percentages
        $salesGrowth = $prevSales > 0 ? (($totalSales - $prevSales) / $prevSales) * 100 : ($totalSales > 0 ? 100 : 0);
        $expenseGrowth = $prevExpenses > 0 ? (($totalExpenses - $prevExpenses) / $prevExpenses) * 100 : ($totalExpenses > 0 ? 100 : 0);
        $profitGrowth = $prevProfit != 0 ? (($netProfit - $prevProfit) / abs($prevProfit)) * 100 : ($netProfit > 0 ? 100 : ($netProfit < 0 ? -100 : 0));

        // Daily/Weekly/Monthly trend data
        $trendData = $this->getTrendData($dateFrom, $dateTo, $period, $branchId);

        // Top expense categories
        $topExpenseCategories = $this->getTopExpenseCategories($dateFrom, $dateTo, $branchId);

        // Payment method breakdown
        $paymentBreakdown = $this->getPaymentBreakdown($dateFrom, $dateTo, $branchId);

        // Branch comparison (only if no specific branch selected)
        $branchComparison = null;
        if (!$branchId && $branches->count() > 1) {
            $branchComparison = $this->getBranchComparison($dateFrom, $dateTo);
        }

        // Best and worst performing days
        $performanceData = $this->getPerformanceData($dateFrom, $dateTo, $branchId);

        return view('analytics.profit.index', compact(
            'branches',
            'branchId',
            'period',
            'dateFrom',
            'dateTo',
            'totalSales',
            'totalExpenses',
            'netProfit',
            'profitMargin',
            'transactionCount',
            'expenseCount',
            'avgTransactionValue',
            'salesGrowth',
            'expenseGrowth',
            'profitGrowth',
            'prevSales',
            'prevExpenses',
            'prevProfit',
            'trendData',
            'topExpenseCategories',
            'paymentBreakdown',
            'branchComparison',
            'performanceData'
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
            $sales = Transaction::completed()
                ->where('branch_id', $branch->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('total');

            $expenses = Expense::where('branch_id', $branch->id)
                ->whereBetween('expense_date', [$dateFrom, $dateTo])
                ->selectRaw('SUM(amount * quantity) as total')
                ->value('total') ?? 0;

            $transactions = Transaction::completed()
                ->where('branch_id', $branch->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count();

            $profit = $sales - $expenses;
            $margin = $sales > 0 ? ($profit / $sales) * 100 : 0;

            return [
                'branch' => $branch,
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $profit,
                'margin' => $margin,
                'transactions' => $transactions,
                'avg_transaction' => $transactions > 0 ? $sales / $transactions : 0,
            ];
        })->sortByDesc('profit')->values();

        // Calculate totals
        $totalSales = $branchData->sum('sales');
        $totalExpenses = $branchData->sum('expenses');
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
            'totalExpenses',
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

    private function getTrendData(string $dateFrom, string $dateTo, string $period, ?int $branchId): array
    {
        $salesQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        $expenseQuery = Expense::whereBetween('expense_date', [$dateFrom, $dateTo]);

        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
            $expenseQuery->where('branch_id', $branchId);
        }

        $groupBy = $period === 'today' ? 'HOUR' : 'DATE';
        $format = $period === 'today' ? '%H:00' : '%Y-%m-%d';

        $dailySales = (clone $salesQuery)
            ->selectRaw("{$groupBy}(created_at) as period, SUM(total) as amount")
            ->groupBy('period')
            ->pluck('amount', 'period')
            ->toArray();

        $dailyExpenses = (clone $expenseQuery)
            ->selectRaw("{$groupBy}(expense_date) as period, SUM(amount * quantity) as amount")
            ->groupBy('period')
            ->pluck('amount', 'period')
            ->toArray();

        $allPeriods = array_unique(array_merge(array_keys($dailySales), array_keys($dailyExpenses)));
        sort($allPeriods);

        return collect($allPeriods)->map(function ($p) use ($dailySales, $dailyExpenses) {
            $sales = $dailySales[$p] ?? 0;
            $expenses = $dailyExpenses[$p] ?? 0;
            return [
                'period' => $p,
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $sales - $expenses,
            ];
        })->toArray();
    }

    private function getTopExpenseCategories(string $dateFrom, string $dateTo, ?int $branchId): \Illuminate\Support\Collection
    {
        $query = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category, SUM(expenses.amount * expenses.quantity) as total')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->limit(5);

        if ($branchId) {
            $query->where('expenses.branch_id', $branchId);
        }

        return $query->get();
    }

    private function getPaymentBreakdown(string $dateFrom, string $dateTo, ?int $branchId): \Illuminate\Support\Collection
    {
        $query = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    private function getBranchComparison(string $dateFrom, string $dateTo): \Illuminate\Support\Collection
    {
        $branches = Branch::active()->get();

        return $branches->map(function ($branch) use ($dateFrom, $dateTo) {
            $sales = Transaction::completed()
                ->where('branch_id', $branch->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('total');

            $expenses = Expense::where('branch_id', $branch->id)
                ->whereBetween('expense_date', [$dateFrom, $dateTo])
                ->selectRaw('SUM(amount * quantity) as total')
                ->value('total') ?? 0;

            return [
                'name' => $branch->name,
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $sales - $expenses,
            ];
        })->sortByDesc('profit')->values();
    }

    private function getPerformanceData(string $dateFrom, string $dateTo, ?int $branchId): array
    {
        $salesQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        $expenseQuery = Expense::whereBetween('expense_date', [$dateFrom, $dateTo]);

        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
            $expenseQuery->where('branch_id', $branchId);
        }

        $dailySales = (clone $salesQuery)
            ->selectRaw('DATE(created_at) as date, SUM(total) as amount')
            ->groupBy('date')
            ->pluck('amount', 'date')
            ->toArray();

        $dailyExpenses = (clone $expenseQuery)
            ->selectRaw('DATE(expense_date) as date, SUM(amount * quantity) as amount')
            ->groupBy('date')
            ->pluck('amount', 'date')
            ->toArray();

        $allDates = array_unique(array_merge(array_keys($dailySales), array_keys($dailyExpenses)));

        $dailyProfit = collect($allDates)->map(function ($date) use ($dailySales, $dailyExpenses) {
            $sales = $dailySales[$date] ?? 0;
            $expenses = $dailyExpenses[$date] ?? 0;
            return [
                'date' => $date,
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $sales - $expenses,
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

            $salesQuery = Transaction::completed()
                ->whereBetween('created_at', [$from, $to . ' 23:59:59']);
            $expenseQuery = Expense::whereBetween('expense_date', [$from, $to]);

            if ($branchId) {
                $salesQuery->where('branch_id', $branchId);
                $expenseQuery->where('branch_id', $branchId);
            }

            $sales = $salesQuery->sum('total');
            $expenses = $expenseQuery->selectRaw('SUM(amount * quantity) as total')->value('total') ?? 0;

            $months->push([
                'month' => $date->format('M Y'),
                'month_short' => $date->format('M'),
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $sales - $expenses,
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

            $salesQuery = Transaction::completed()
                ->whereBetween('created_at', [$from, $to . ' 23:59:59']);
            $expenseQuery = Expense::whereBetween('expense_date', [$from, $to]);

            if ($branchId) {
                $salesQuery->where('branch_id', $branchId);
                $expenseQuery->where('branch_id', $branchId);
            }

            $sales = $salesQuery->sum('total');
            $expenses = $expenseQuery->selectRaw('SUM(amount * quantity) as total')->value('total') ?? 0;

            $weeks->push([
                'week' => 'Week ' . $date->weekOfMonth,
                'period' => $from . ' - ' . $to,
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $sales - $expenses,
            ]);
        }

        return $weeks;
    }

    private function getYearComparison(?int $branchId): array
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        $currentYearSalesQuery = Transaction::completed()
            ->whereYear('created_at', $currentYear);
        $currentYearExpenseQuery = Expense::whereYear('expense_date', $currentYear);

        $lastYearSalesQuery = Transaction::completed()
            ->whereYear('created_at', $lastYear);
        $lastYearExpenseQuery = Expense::whereYear('expense_date', $lastYear);

        if ($branchId) {
            $currentYearSalesQuery->where('branch_id', $branchId);
            $currentYearExpenseQuery->where('branch_id', $branchId);
            $lastYearSalesQuery->where('branch_id', $branchId);
            $lastYearExpenseQuery->where('branch_id', $branchId);
        }

        $currentSales = $currentYearSalesQuery->sum('total');
        $currentExpenses = $currentYearExpenseQuery->selectRaw('SUM(amount * quantity) as total')->value('total') ?? 0;

        $lastSales = $lastYearSalesQuery->sum('total');
        $lastExpenses = $lastYearExpenseQuery->selectRaw('SUM(amount * quantity) as total')->value('total') ?? 0;

        return [
            'current' => [
                'year' => $currentYear,
                'sales' => $currentSales,
                'expenses' => $currentExpenses,
                'profit' => $currentSales - $currentExpenses,
            ],
            'last' => [
                'year' => $lastYear,
                'sales' => $lastSales,
                'expenses' => $lastExpenses,
                'profit' => $lastSales - $lastExpenses,
            ],
            'growth' => $lastSales > 0 ? (($currentSales - $lastSales) / $lastSales) * 100 : 0,
        ];
    }
}
