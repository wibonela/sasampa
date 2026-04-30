<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Single source of truth for profit math used by web Profit Analytics,
 * mobile API, and any future report. All public methods are pure with
 * respect to the filter parameters — caller passes a normalized window.
 */
class ProfitBreakdownService
{
    /**
     * Resolve a period keyword into [from, to, previousFrom, previousTo] dates.
     * Custom period reads date_from / date_to from the filter array.
     */
    public function resolvePeriod(string $period, array $filters = []): array
    {
        $customFrom = $filters['date_from'] ?? null;
        $customTo = $filters['date_to'] ?? null;

        if ($period === 'custom' && $customFrom && $customTo) {
            $from = Carbon::parse($customFrom)->startOfDay();
            $to = Carbon::parse($customTo)->endOfDay();
            $diffDays = (int) $from->diffInDays($to);

            return [
                'from' => $from,
                'to' => $to,
                'previous_from' => $from->copy()->subDays($diffDays + 1),
                'previous_to' => $from->copy()->subDay()->endOfDay(),
                'label' => $from->format('M j') . ' - ' . $to->format('M j, Y'),
            ];
        }

        return match ($period) {
            'today' => [
                'from' => now()->startOfDay(),
                'to' => now()->endOfDay(),
                'previous_from' => now()->subDay()->startOfDay(),
                'previous_to' => now()->subDay()->endOfDay(),
                'label' => 'Today',
            ],
            'week' => [
                'from' => now()->startOfWeek(),
                'to' => now()->endOfDay(),
                'previous_from' => now()->subWeek()->startOfWeek(),
                'previous_to' => now()->subWeek()->endOfWeek(),
                'label' => 'This Week',
            ],
            'quarter' => [
                'from' => now()->startOfQuarter(),
                'to' => now()->endOfDay(),
                'previous_from' => now()->subQuarter()->startOfQuarter(),
                'previous_to' => now()->subQuarter()->endOfQuarter(),
                'label' => 'This Quarter',
            ],
            'year' => [
                'from' => now()->startOfYear(),
                'to' => now()->endOfDay(),
                'previous_from' => now()->subYear()->startOfYear(),
                'previous_to' => now()->subYear()->endOfYear(),
                'label' => 'This Year',
            ],
            default => [
                'from' => now()->startOfMonth(),
                'to' => now()->endOfDay(),
                'previous_from' => now()->subMonth()->startOfMonth(),
                'previous_to' => now()->subMonth()->endOfMonth(),
                'label' => 'This Month',
            ],
        };
    }

    /**
     * Top-level summary KPIs for the window.
     */
    public function summary(
        Carbon $from,
        Carbon $to,
        ?int $branchId,
        bool $includeExpenses,
        array $expenseCategoryIds = [],
    ): array {
        $salesQuery = $this->salesQuery($from, $to, $branchId);

        $revenue = (float) (clone $salesQuery)->sum('total');
        $transactionIds = (clone $salesQuery)->pluck('id');

        $cogs = (float) TransactionItem::whereIn('transaction_id', $transactionIds)
            ->selectRaw('SUM(cost_price * quantity) as total')
            ->value('total') ?? 0.0;

        $grossProfit = $revenue - $cogs;

        $operatingExpenses = $this->operatingExpenses($from, $to, $branchId, $expenseCategoryIds);

        $netProfit = $includeExpenses
            ? $grossProfit - $operatingExpenses
            : $grossProfit;

        $grossMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $netMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return [
            'revenue' => round($revenue, 2),
            'cogs' => round($cogs, 2),
            'gross_profit' => round($grossProfit, 2),
            'operating_expenses' => round($operatingExpenses, 2),
            'net_profit' => round($netProfit, 2),
            'gross_margin' => round($grossMargin, 2),
            'net_margin' => round($netMargin, 2),
            'transactions_count' => (int) $transactionIds->count(),
            'include_expenses' => $includeExpenses,
        ];
    }

    /**
     * Daily series. For 'today' period, returns hourly buckets.
     */
    public function trend(
        Carbon $from,
        Carbon $to,
        ?int $branchId,
        bool $includeExpenses,
        array $expenseCategoryIds = [],
        bool $hourly = false,
    ): array {
        $bucket = $hourly ? 'HOUR' : 'DATE';

        $sales = (clone $this->salesQuery($from, $to, $branchId))
            ->selectRaw("{$bucket}(created_at) as bucket, SUM(total) as amount")
            ->groupBy('bucket')
            ->pluck('amount', 'bucket')
            ->toArray();

        $cogs = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->where(fn ($q) => $q->where('transactions.type', 'sale')->orWhereNull('transactions.type'))
            ->whereBetween('transactions.created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('transactions.branch_id', $branchId))
            ->selectRaw("{$bucket}(transactions.created_at) as bucket, SUM(transaction_items.cost_price * transaction_items.quantity) as amount")
            ->groupBy('bucket')
            ->pluck('amount', 'bucket')
            ->toArray();

        $buckets = array_unique(array_merge(array_keys($sales), array_keys($cogs)));
        sort($buckets);

        // Prorate operating expenses across the window evenly per bucket so the
        // trend reflects the same Net Profit definition the summary uses.
        $totalExpenses = $includeExpenses
            ? $this->operatingExpenses($from, $to, $branchId, $expenseCategoryIds)
            : 0.0;
        $perBucketExpense = count($buckets) > 0 ? $totalExpenses / count($buckets) : 0.0;

        return collect($buckets)->map(function ($b) use ($sales, $cogs, $perBucketExpense, $hourly) {
            $r = (float) ($sales[$b] ?? 0);
            $c = (float) ($cogs[$b] ?? 0);
            $gp = $r - $c;
            return [
                'bucket' => $hourly ? sprintf('%02d:00', $b) : $b,
                'revenue' => round($r, 2),
                'cogs' => round($c, 2),
                'expenses' => round($perBucketExpense, 2),
                'gross_profit' => round($gp, 2),
                'net_profit' => round($gp - $perBucketExpense, 2),
            ];
        })->values()->toArray();
    }

    /**
     * Operating expenses split by category, ordered by amount desc.
     */
    public function expensesByCategory(
        Carbon $from,
        Carbon $to,
        ?int $branchId,
        array $expenseCategoryIds = [],
    ): array {
        $expenses = Expense::with('category')
            ->overlappingPeriod($from, $to)
            ->when($branchId, fn ($q) => $q->where('expenses.branch_id', $branchId))
            ->when(!empty($expenseCategoryIds), fn ($q) => $q->whereIn('expense_category_id', $expenseCategoryIds))
            ->get();

        $byCategory = [];
        foreach ($expenses as $expense) {
            $allocated = $expense->proratedAmount($from, $to);
            if ($allocated <= 0) continue;
            $key = $expense->expense_category_id ?? 0;
            if (!isset($byCategory[$key])) {
                $byCategory[$key] = [
                    'id' => $expense->expense_category_id,
                    'name' => $expense->category?->name ?? 'Uncategorized',
                    'amount' => 0.0,
                ];
            }
            $byCategory[$key]['amount'] += $allocated;
        }

        $total = array_sum(array_column($byCategory, 'amount'));

        return collect($byCategory)
            ->map(fn ($c) => [
                'id' => $c['id'],
                'name' => $c['name'],
                'amount' => round($c['amount'], 2),
                'percentage' => $total > 0 ? round(($c['amount'] / $total) * 100, 1) : 0,
            ])
            ->sortByDesc('amount')
            ->values()
            ->toArray();
    }

    /**
     * Per-branch revenue + net profit. Returns empty when single-branch or
     * branches feature is disabled.
     */
    public function branchComparison(
        Carbon $from,
        Carbon $to,
        bool $includeExpenses,
        array $expenseCategoryIds = [],
    ): array {
        $branches = Branch::active()->orderBy('name')->get();
        if ($branches->count() < 2) {
            return [];
        }

        return $branches->map(function (Branch $branch) use ($from, $to, $includeExpenses, $expenseCategoryIds) {
            $sum = $this->summary($from, $to, $branch->id, $includeExpenses, $expenseCategoryIds);
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'revenue' => $sum['revenue'],
                'gross_profit' => $sum['gross_profit'],
                'operating_expenses' => $sum['operating_expenses'],
                'net_profit' => $sum['net_profit'],
                'net_margin' => $sum['net_margin'],
            ];
        })->sortByDesc('net_profit')->values()->toArray();
    }

    /**
     * Top products ranked by gross profit ((selling - cost) * qty) for the window.
     */
    public function topProductsByProfit(
        Carbon $from,
        Carbon $to,
        ?int $branchId,
        int $limit = 10,
    ): array {
        $rows = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->where(fn ($q) => $q->where('transactions.type', 'sale')->orWhereNull('transactions.type'))
            ->whereBetween('transactions.created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('transactions.branch_id', $branchId))
            ->selectRaw('
                transaction_items.product_id,
                MAX(transaction_items.product_name) as product_name,
                SUM(transaction_items.quantity) as quantity,
                SUM(transaction_items.subtotal) as revenue,
                SUM(transaction_items.cost_price * transaction_items.quantity) as cogs
            ')
            ->groupBy('transaction_items.product_id')
            ->orderByRaw('SUM(transaction_items.subtotal - (transaction_items.cost_price * transaction_items.quantity)) DESC')
            ->limit($limit)
            ->get();

        return $rows->map(function ($r) {
            $revenue = (float) $r->revenue;
            $cogs = (float) $r->cogs;
            $gp = $revenue - $cogs;
            return [
                'product_id' => $r->product_id,
                'name' => $r->product_name,
                'quantity' => (float) $r->quantity,
                'revenue' => round($revenue, 2),
                'cogs' => round($cogs, 2),
                'gross_profit' => round($gp, 2),
                'margin' => $revenue > 0 ? round(($gp / $revenue) * 100, 1) : 0,
            ];
        })->toArray();
    }

    /**
     * Convenience: full comprehensive payload for the mobile API.
     */
    public function fullReport(
        string $period,
        array $filters,
        ?int $branchId,
        bool $includeExpenses,
        array $expenseCategoryIds = [],
        int $topProductsLimit = 10,
    ): array {
        $window = $this->resolvePeriod($period, $filters);
        /** @var Carbon $from */ $from = $window['from'];
        /** @var Carbon $to */ $to = $window['to'];

        return [
            'period' => [
                'key' => $period,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'label' => $window['label'],
            ],
            'summary' => $this->summary($from, $to, $branchId, $includeExpenses, $expenseCategoryIds),
            'trend' => $this->trend($from, $to, $branchId, $includeExpenses, $expenseCategoryIds, $period === 'today'),
            'expenses_by_category' => $this->expensesByCategory($from, $to, $branchId, $expenseCategoryIds),
            'branches' => $branchId ? [] : $this->branchComparison($from, $to, $includeExpenses, $expenseCategoryIds),
            'top_products_by_profit' => $this->topProductsByProfit($from, $to, $branchId, $topProductsLimit),
        ];
    }

    private function salesQuery(Carbon $from, Carbon $to, ?int $branchId)
    {
        return Transaction::completed()->sales()
            ->whereBetween('created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }

    private function operatingExpenses(
        Carbon $from,
        Carbon $to,
        ?int $branchId,
        array $expenseCategoryIds,
    ): float {
        return Expense::proratedSum($from, $to, function ($q) use ($branchId, $expenseCategoryIds) {
            if ($branchId) {
                $q->where('expenses.branch_id', $branchId);
            }
            if (!empty($expenseCategoryIds)) {
                $q->whereIn('expense_category_id', $expenseCategoryIds);
            }
        });
    }
}
