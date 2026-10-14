<?php

namespace App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Per-company sales activity for the platform admin.
 *
 * A "sale" is a completed transaction of type sale (or legacy rows with a null type).
 * Orders, quotes, pending, voided and refunded rows never count as revenue.
 */
class CompanyActivityService
{
    /** Sold within this many days = "active". */
    public const ACTIVE_DAYS = 7;

    /** Minimum sales in the recent window before a company can rank as "trending". */
    public const TRENDING_MIN_SALES = 5;

    private function sales()
    {
        return DB::table('transactions')
            ->where('transactions.status', 'completed')
            ->where(fn ($q) => $q->where('transactions.type', 'sale')->orWhereNull('transactions.type'));
    }

    /**
     * Add last_sale_at, sales_7d, revenue_7d, revenue_30d and sales_30d to a company query.
     */
    public function withActivity(Builder $query): Builder
    {
        $d7 = now()->subDays(7)->toDateTimeString();
        $d30 = now()->subDays(30)->toDateTimeString();

        $base = fn () => $this->sales()->whereColumn('transactions.company_id', 'companies.id');

        return $query
            ->addSelect('companies.*')
            ->selectSub($base()->selectRaw('MAX(transactions.created_at)'), 'last_sale_at')
            ->selectSub($base()->selectRaw('COUNT(*)')->where('transactions.created_at', '>=', $d7), 'sales_7d')
            ->selectSub($base()->selectRaw('COALESCE(SUM(transactions.total), 0)')->where('transactions.created_at', '>=', $d7), 'revenue_7d')
            ->selectSub($base()->selectRaw('COUNT(*)')->where('transactions.created_at', '>=', $d30), 'sales_30d')
            ->selectSub($base()->selectRaw('COALESCE(SUM(transactions.total), 0)')->where('transactions.created_at', '>=', $d30), 'revenue_30d');
    }

    /** Restrict a company query to those who sold (or did not sell) in the last 7 days. */
    public function filterByActivity(Builder $query, string $state): Builder
    {
        $since = now()->subDays(self::ACTIVE_DAYS)->toDateTimeString();
        $exists = fn () => $this->sales()
            ->whereColumn('transactions.company_id', 'companies.id')
            ->where('transactions.created_at', '>=', $since);

        return match ($state) {
            'active' => $query->whereExists($exists()),
            'inactive' => $query->where('companies.status', Company::STATUS_APPROVED)->whereNotExists($exists()),
            default => $query,
        };
    }

    /** Health label from the last sale time. */
    public static function health(?string $lastSaleAt, ?Carbon $createdAt = null): array
    {
        if (! $lastSaleAt) {
            return ['label' => 'No sales yet', 'class' => 'bg-secondary', 'days' => null];
        }

        $days = (int) Carbon::parse($lastSaleAt)->diffInDays(now());

        return match (true) {
            $days <= 2 => ['label' => 'Active', 'class' => 'bg-success', 'days' => $days],
            $days <= self::ACTIVE_DAYS => ['label' => 'Slowing', 'class' => 'bg-warning', 'days' => $days],
            default => ['label' => 'Inactive', 'class' => 'bg-danger', 'days' => $days],
        };
    }

    /**
     * Companies whose revenue over the last 7 days beats the 7 days before.
     * Ranked by absolute growth; needs a minimum sales volume to be listed.
     */
    public function trendingCompanies(int $limit = 5): array
    {
        return $this->movers($limit, 'desc');
    }

    /** Companies whose weekly revenue fell the most versus the previous week. */
    public function slippingCompanies(int $limit = 5): array
    {
        return $this->movers($limit, 'asc');
    }

    private function movers(int $limit, string $direction): array
    {
        $now = now();
        $w1 = $now->copy()->subDays(7)->toDateTimeString();
        $w2 = $now->copy()->subDays(14)->toDateTimeString();

        $rows = $this->sales()
            ->join('companies', 'companies.id', '=', 'transactions.company_id')
            ->where('companies.status', Company::STATUS_APPROVED)
            ->where('transactions.created_at', '>=', $w2)
            ->groupBy('companies.id', 'companies.name')
            ->selectRaw('companies.id, companies.name')
            ->selectRaw('COALESCE(SUM(CASE WHEN transactions.created_at >= ? THEN transactions.total END), 0) as this_week', [$w1])
            ->selectRaw('COALESCE(SUM(CASE WHEN transactions.created_at < ? THEN transactions.total END), 0) as last_week', [$w1])
            ->selectRaw('SUM(CASE WHEN transactions.created_at >= ? THEN 1 ELSE 0 END) as sales_this_week', [$w1])
            ->selectRaw('SUM(CASE WHEN transactions.created_at < ? THEN 1 ELSE 0 END) as sales_last_week', [$w1])
            ->get();

        return $rows
            ->filter(fn ($r) => $direction === 'desc'
                ? $r->this_week > $r->last_week && $r->sales_this_week >= self::TRENDING_MIN_SALES
                : $r->this_week < $r->last_week && $r->sales_last_week >= self::TRENDING_MIN_SALES)
            ->map(function ($r) {
                $r->change = $r->this_week - $r->last_week;
                $r->change_pct = $r->last_week > 0 ? round(($r->change / $r->last_week) * 100) : null;

                return $r;
            })
            ->sortBy('change', SORT_REGULAR, $direction === 'desc')
            ->take($limit)
            ->values()
            ->all();
    }

    /** Count of approved companies that sold in the last 7 days. */
    public function sellingThisWeek(): int
    {
        return $this->sales()
            ->join('companies', 'companies.id', '=', 'transactions.company_id')
            ->where('companies.status', Company::STATUS_APPROVED)
            ->where('transactions.created_at', '>=', now()->subDays(self::ACTIVE_DAYS)->toDateTimeString())
            ->distinct()
            ->count('transactions.company_id');
    }

    /**
     * Last-sale time for a set of company ids, keyed by id.
     */
    public function lastSaleTimes(iterable $companyIds): array
    {
        return $this->sales()
            ->whereIn('transactions.company_id', collect($companyIds)->all())
            ->groupBy('transactions.company_id')
            ->selectRaw('transactions.company_id, MAX(transactions.created_at) as last_sale_at')
            ->pluck('last_sale_at', 'company_id')
            ->all();
    }

    /**
     * Everything the admin needs to see what one company is selling.
     */
    public function companySales(Company $company, int $days = 30): array
    {
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $since = now()->subDays($days - 1)->startOfDay();
        $prevSince = $since->copy()->subDays($days);
        $scoped = fn () => $this->sales()->where('transactions.company_id', $company->id);

        $current = $scoped()->where('transactions.created_at', '>=', $since->toDateTimeString())
            ->selectRaw('COUNT(*) as sales, COALESCE(SUM(transactions.total), 0) as revenue')->first();
        $previous = $scoped()
            ->where('transactions.created_at', '>=', $prevSince->toDateTimeString())
            ->where('transactions.created_at', '<', $since->toDateTimeString())
            ->selectRaw('COUNT(*) as sales, COALESCE(SUM(transactions.total), 0) as revenue')->first();
        $allTime = $scoped()->selectRaw('COUNT(*) as sales, COALESCE(SUM(transactions.total), 0) as revenue, MAX(transactions.created_at) as last_sale_at')->first();
        $today = $scoped()->where('transactions.created_at', '>=', now()->startOfDay()->toDateTimeString())
            ->selectRaw('COUNT(*) as sales, COALESCE(SUM(transactions.total), 0) as revenue')->first();

        // Daily series, zero-filled
        $byDay = $scoped()->where('transactions.created_at', '>=', $since->toDateTimeString())
            ->selectRaw('date(transactions.created_at) as day, SUM(transactions.total) as revenue, COUNT(*) as sales')
            ->groupBy('day')->get()->keyBy('day');
        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $since->copy()->addDays($i);
            $row = $byDay->get($d->toDateString());
            $series[] = [
                'label' => $d->format('M j'),
                'revenue' => (float) ($row->revenue ?? 0),
                'sales' => (int) ($row->sales ?? 0),
            ];
        }

        $topProducts = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.company_id', $company->id)
            ->where('transactions.status', 'completed')
            ->where(fn ($q) => $q->where('transactions.type', 'sale')->orWhereNull('transactions.type'))
            ->where('transactions.created_at', '>=', $since->toDateTimeString())
            ->groupBy('transaction_items.product_name')
            ->selectRaw('transaction_items.product_name as name, SUM(transaction_items.quantity) as qty, SUM(transaction_items.subtotal) as revenue')
            ->orderByDesc('revenue')->limit(10)->get();

        $payments = $scoped()->where('transactions.created_at', '>=', $since->toDateTimeString())
            ->groupBy('transactions.payment_method')
            ->selectRaw('transactions.payment_method as method, COUNT(*) as sales, SUM(transactions.total) as revenue')
            ->orderByDesc('revenue')->get();

        $cashiers = $scoped()->where('transactions.created_at', '>=', $since->toDateTimeString())
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->groupBy('transactions.user_id', 'users.name')
            ->selectRaw('COALESCE(users.name, \'Unknown\') as name, COUNT(*) as sales, SUM(transactions.total) as revenue')
            ->orderByDesc('revenue')->limit(10)->get();

        $branches = $scoped()->where('transactions.created_at', '>=', $since->toDateTimeString())
            ->leftJoin('branches', 'branches.id', '=', 'transactions.branch_id')
            ->groupBy('transactions.branch_id', 'branches.name')
            ->selectRaw('COALESCE(branches.name, \'Main\') as name, COUNT(*) as sales, SUM(transactions.total) as revenue')
            ->orderByDesc('revenue')->get();

        $recent = $scoped()
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw('transactions.transaction_number, transactions.total, transactions.payment_method, transactions.created_at, users.name as cashier')
            ->orderByDesc('transactions.created_at')->limit(15)->get();

        $lowStock = DB::table('inventory')
            ->join('products', 'products.id', '=', 'inventory.product_id')
            ->where('products.company_id', $company->id)
            ->whereColumn('inventory.quantity', '<=', 'inventory.low_stock_threshold')
            ->selectRaw('products.name, inventory.quantity, inventory.low_stock_threshold')
            ->orderBy('inventory.quantity')->limit(8)->get();

        $growth = $previous->revenue > 0
            ? round((($current->revenue - $previous->revenue) / $previous->revenue) * 100)
            : null;

        return [
            'days' => $days,
            'current' => $current,
            'previous' => $previous,
            'growth_pct' => $growth,
            'all_time' => $allTime,
            'today' => $today,
            'health' => self::health($allTime->last_sale_at),
            'series' => $series,
            'top_products' => $topProducts,
            'payments' => $payments,
            'cashiers' => $cashiers,
            'branches' => $branches,
            'recent' => $recent,
            'low_stock' => $lowStock,
        ];
    }

    /**
     * Companies that did anything today, with a breakdown of what they did.
     * "Active" matches the dashboard card: any transaction created today.
     */
    public function activeToday(): array
    {
        $start = today();

        $tx = DB::table('transactions')
            ->where('created_at', '>=', $start)
            ->get(['id', 'company_id', 'user_id', 'type', 'status', 'total', 'created_at']);

        $companyIds = $tx->pluck('company_id')->unique()->values();

        $companies = Company::with('owner')->whereIn('id', $companyIds)->get()->keyBy('id');

        $users = DB::table('users')
            ->whereIn('id', $tx->pluck('user_id')->filter()->unique())
            ->pluck('name', 'id');

        $items = DB::table('transaction_items')
            ->whereIn('transaction_id', $tx->pluck('id'))
            ->select('transaction_id', 'product_name', 'quantity')
            ->get()
            ->groupBy('transaction_id');

        $expenses = DB::table('expenses')
            ->whereIn('company_id', $companyIds)
            ->where('created_at', '>=', $start)
            ->select('company_id', DB::raw('COUNT(*) as n'), DB::raw('SUM(amount) as total'))
            ->groupBy('company_id')
            ->get()
            ->keyBy('company_id');

        $rows = $tx->groupBy('company_id')->map(function ($group, $companyId) use ($companies, $users, $items, $expenses) {
            $company = $companies->get($companyId);
            if (! $company) {
                return null;
            }

            $isSale = fn ($t) => $t->status === 'completed' && ($t->type === 'sale' || $t->type === null);
            $sales = $group->filter($isSale);

            $products = [];
            foreach ($sales as $t) {
                foreach ($items->get($t->id, []) as $it) {
                    $products[$it->product_name] = ($products[$it->product_name] ?? 0) + (float) $it->quantity;
                }
            }
            arsort($products);

            $other = $group->reject($isSale)
                ->map(fn ($t) => trim(($t->type ?? 'sale') . ' ' . $t->status))
                ->countBy()
                ->all();

            $exp = $expenses->get($companyId);

            return [
                'company' => $company,
                'sales_count' => $sales->count(),
                'revenue' => (float) $sales->sum('total'),
                'last_at' => Carbon::parse($group->max('created_at')),
                'first_at' => Carbon::parse($group->min('created_at')),
                'staff' => $group->pluck('user_id')->filter()->unique()->map(fn ($id) => $users[$id] ?? null)->filter()->values()->all(),
                'top_products' => array_slice($products, 0, 5, true),
                'other' => $other,
                'expenses_count' => $exp->n ?? 0,
                'expenses_total' => (float) ($exp->total ?? 0),
            ];
        })->filter()->sortByDesc('last_at')->values();

        return [
            'rows' => $rows,
            'totals' => [
                'companies' => $rows->count(),
                'sales' => $rows->sum('sales_count'),
                'revenue' => $rows->sum('revenue'),
            ],
        ];
    }
}
