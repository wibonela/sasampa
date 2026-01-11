<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Default cache TTL in seconds (5 minutes)
     */
    protected const DEFAULT_TTL = 300;

    /**
     * Get dashboard statistics with caching
     */
    public function dashboardStats(int $companyId, ?int $branchId = null): array
    {
        $cacheKey = $this->buildKey('dashboard_stats', $companyId, $branchId);

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($companyId, $branchId) {
            return $this->calculateDashboardStats($companyId, $branchId);
        });
    }

    /**
     * Calculate dashboard stats without caching
     */
    protected function calculateDashboardStats(int $companyId, ?int $branchId): array
    {
        $query = Transaction::where('company_id', $companyId)
            ->where('status', 'completed');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Get today's stats in a single query
        $today = now()->toDateString();
        $todayStats = (clone $query)
            ->whereDate('created_at', $today)
            ->selectRaw("
                COALESCE(SUM(total), 0) as total_sales,
                COUNT(*) as transaction_count,
                COALESCE(AVG(total), 0) as avg_transaction
            ")
            ->first();

        // Get this week's stats
        $weekStart = now()->startOfWeek()->toDateString();
        $weekStats = (clone $query)
            ->whereDate('created_at', '>=', $weekStart)
            ->selectRaw("
                COALESCE(SUM(total), 0) as total_sales,
                COUNT(*) as transaction_count
            ")
            ->first();

        // Get this month's stats
        $monthStart = now()->startOfMonth()->toDateString();
        $monthStats = (clone $query)
            ->whereDate('created_at', '>=', $monthStart)
            ->selectRaw("
                COALESCE(SUM(total), 0) as total_sales,
                COUNT(*) as transaction_count
            ")
            ->first();

        // Get low stock count
        $lowStockQuery = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('inventory', function ($q) {
                $q->whereColumn('quantity', '<=', 'low_stock_threshold');
            });

        if ($branchId) {
            $lowStockQuery->where('branch_id', $branchId);
        }

        $lowStockCount = $lowStockQuery->count();

        return [
            'today' => [
                'sales' => $todayStats->total_sales ?? 0,
                'transactions' => $todayStats->transaction_count ?? 0,
                'average' => $todayStats->avg_transaction ?? 0,
            ],
            'week' => [
                'sales' => $weekStats->total_sales ?? 0,
                'transactions' => $weekStats->transaction_count ?? 0,
            ],
            'month' => [
                'sales' => $monthStats->total_sales ?? 0,
                'transactions' => $monthStats->transaction_count ?? 0,
            ],
            'low_stock_count' => $lowStockCount,
        ];
    }

    /**
     * Get top products with caching
     */
    public function topProducts(int $companyId, ?int $branchId = null, int $limit = 5): array
    {
        $cacheKey = $this->buildKey('top_products', $companyId, $branchId, $limit);

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($companyId, $branchId, $limit) {
            $today = now()->toDateString();

            $query = Product::where('products.company_id', $companyId)
                ->where('products.is_active', true)
                ->withCount(['transactionItems as sold_today' => function ($q) use ($today, $branchId) {
                    $q->whereHas('transaction', function ($tq) use ($today, $branchId) {
                        $tq->where('status', 'completed')
                            ->whereDate('created_at', $today);
                        if ($branchId) {
                            $tq->where('branch_id', $branchId);
                        }
                    });
                }])
                ->orderByDesc('sold_today')
                ->limit($limit);

            if ($branchId) {
                $query->where('products.branch_id', $branchId);
            }

            return $query->get(['id', 'name', 'selling_price'])->toArray();
        });
    }

    /**
     * Get recent transactions with caching
     */
    public function recentTransactions(int $companyId, ?int $branchId = null, int $limit = 5): array
    {
        $cacheKey = $this->buildKey('recent_transactions', $companyId, $branchId, $limit);

        return Cache::remember($cacheKey, 60, function () use ($companyId, $branchId, $limit) {
            $query = Transaction::where('company_id', $companyId)
                ->with(['user:id,name'])
                ->select('id', 'transaction_number', 'total', 'status', 'user_id', 'created_at')
                ->orderByDesc('created_at')
                ->limit($limit);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            return $query->get()->toArray();
        });
    }

    /**
     * Invalidate dashboard cache for a company
     */
    public function invalidateDashboard(int $companyId, ?int $branchId = null): void
    {
        $keys = [
            $this->buildKey('dashboard_stats', $companyId, $branchId),
            $this->buildKey('top_products', $companyId, $branchId, 5),
            $this->buildKey('recent_transactions', $companyId, $branchId, 5),
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Also invalidate company-wide cache if branch-specific
        if ($branchId) {
            Cache::forget($this->buildKey('dashboard_stats', $companyId, null));
            Cache::forget($this->buildKey('top_products', $companyId, null, 5));
            Cache::forget($this->buildKey('recent_transactions', $companyId, null, 5));
        }
    }

    /**
     * Invalidate all caches for a company
     */
    public function invalidateCompany(int $companyId): void
    {
        $this->invalidateDashboard($companyId);
        // Add other cache invalidations as needed
    }

    /**
     * Build a cache key
     */
    protected function buildKey(string $prefix, int $companyId, ?int $branchId = null, ...$extra): string
    {
        $parts = [$prefix, "company:{$companyId}"];

        if ($branchId) {
            $parts[] = "branch:{$branchId}";
        }

        if (!empty($extra)) {
            $parts[] = implode(':', $extra);
        }

        return implode(':', $parts);
    }
}
