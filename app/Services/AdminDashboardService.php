<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\MobileAppRequest;
use App\Models\MobileDevice;
use App\Models\Product;
use App\Models\Sanduku;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\WhatsappReceiptLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDashboardService
{
    public function getCompanyStats(): array
    {
        return [
            'total' => Company::count(),
            'pending' => Company::where('status', Company::STATUS_PENDING)->count(),
            'approved' => Company::where('status', Company::STATUS_APPROVED)->count(),
            'rejected' => Company::where('status', Company::STATUS_REJECTED)->count(),
        ];
    }

    public function getTotalUsers(): int
    {
        return User::count();
    }

    public function getUsersByRole(): array
    {
        return [
            'platform_admin' => User::where('role', User::ROLE_PLATFORM_ADMIN)->count(),
            'company_owner' => User::where('role', User::ROLE_COMPANY_OWNER)->count(),
            'cashier' => User::where('role', User::ROLE_CASHIER)->count(),
        ];
    }

    public function getTransactionStats(): array
    {
        $total = Transaction::withoutGlobalScope('company')->sum('total');
        $todayTotal = Transaction::withoutGlobalScope('company')
            ->whereDate('created_at', today())
            ->sum('total');
        $count = Transaction::withoutGlobalScope('company')->count();
        $todayCount = Transaction::withoutGlobalScope('company')
            ->whereDate('created_at', today())
            ->count();

        return [
            'total_revenue' => $total,
            'today_revenue' => $todayTotal,
            'total_transactions' => $count,
            'today_transactions' => $todayCount,
        ];
    }

    public function getRegistrationTrends(int $days = 30): array
    {
        $data = Company::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $values[] = $data->get($date)?->count ?? 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function getRevenueTrends(string $period = 'daily', int $limit = 30): array
    {
        $query = Transaction::withoutGlobalScope('company')
            ->where('status', 'completed');

        switch ($period) {
            case 'weekly':
                // Use strftime for SQLite compatibility
                $selectPeriod = DB::raw("strftime('%Y%W', created_at) as period");
                $query->where('created_at', '>=', now()->subWeeks($limit));
                break;
            case 'monthly':
                // Use strftime for SQLite compatibility
                $selectPeriod = DB::raw("strftime('%Y-%m', created_at) as period");
                $query->where('created_at', '>=', now()->subMonths($limit));
                break;
            default: // daily
                $selectPeriod = DB::raw("strftime('%Y-%m-%d', created_at) as period");
                $query->where('created_at', '>=', now()->subDays($limit));
        }

        $data = $query->select(
            $selectPeriod,
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as transactions')
        )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $labels = [];
        $revenues = [];
        $transactions = [];

        if ($period === 'daily') {
            for ($i = $limit - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $labels[] = now()->subDays($i)->format('M d');
                $revenues[] = (float) ($data->get($date)?->revenue ?? 0);
                $transactions[] = (int) ($data->get($date)?->transactions ?? 0);
            }
        } elseif ($period === 'weekly') {
            for ($i = $limit - 1; $i >= 0; $i--) {
                $week = now()->subWeeks($i);
                $yearWeek = $week->format('oW');
                $labels[] = 'Week ' . $week->format('W');
                $revenues[] = (float) ($data->get($yearWeek)?->revenue ?? 0);
                $transactions[] = (int) ($data->get($yearWeek)?->transactions ?? 0);
            }
        } else { // monthly
            for ($i = $limit - 1; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $monthKey = $month->format('Y-m');
                $labels[] = $month->format('M Y');
                $revenues[] = (float) ($data->get($monthKey)?->revenue ?? 0);
                $transactions[] = (int) ($data->get($monthKey)?->transactions ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'transactions' => $transactions,
        ];
    }

    public function getTopCompanies(int $limit = 5): array
    {
        return Company::select('companies.*')
            ->selectRaw('COALESCE(SUM(transactions.total), 0) as total_revenue')
            ->selectRaw('COUNT(transactions.id) as transaction_count')
            ->leftJoin('transactions', 'companies.id', '=', 'transactions.company_id')
            ->where('companies.status', Company::STATUS_APPROVED)
            ->groupBy('companies.id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'total_revenue' => $company->total_revenue,
                    'transaction_count' => $company->transaction_count,
                ];
            })
            ->toArray();
    }

    public function getRecentActivities(int $limit = 10): array
    {
        $activities = [];

        // Recent company registrations
        $recentCompanies = Company::latest()->limit(5)->get();
        foreach ($recentCompanies as $company) {
            $activities[] = [
                'type' => 'company_registered',
                'icon' => 'bi-building-add',
                'color' => 'primary',
                'message' => "{$company->name} registered",
                'timestamp' => $company->created_at,
            ];
        }

        // Recent approvals
        $recentApprovals = Company::where('status', Company::STATUS_APPROVED)
            ->whereNotNull('approved_at')
            ->latest('approved_at')
            ->limit(5)
            ->get();
        foreach ($recentApprovals as $company) {
            $activities[] = [
                'type' => 'company_approved',
                'icon' => 'bi-check-circle',
                'color' => 'success',
                'message' => "{$company->name} was approved",
                'timestamp' => $company->approved_at,
            ];
        }

        // Recent transactions (across all companies)
        $recentTransactions = Transaction::withoutGlobalScope('company')
            ->with('user.company')
            ->latest()
            ->limit(5)
            ->get();
        foreach ($recentTransactions as $transaction) {
            $companyName = $transaction->user?->company?->name ?? 'Unknown';
            $activities[] = [
                'type' => 'transaction',
                'icon' => 'bi-receipt',
                'color' => 'info',
                'message' => "Transaction #{$transaction->transaction_number} at {$companyName}",
                'timestamp' => $transaction->created_at,
                'amount' => $transaction->total,
            ];
        }

        // Sort by timestamp and limit
        usort($activities, fn($a, $b) => $b['timestamp']->timestamp <=> $a['timestamp']->timestamp);

        return array_slice($activities, 0, $limit);
    }

    public function getSystemHealth(): array
    {
        $health = [
            'database' => $this->checkDatabaseConnection(),
            'storage' => $this->checkStorageUsage(),
            'errors' => $this->getRecentErrorCount(),
        ];

        $health['overall'] = $this->calculateOverallHealth($health);

        return $health;
    }

    private function checkDatabaseConnection(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'healthy',
                'message' => 'Database connection is working',
                'color' => 'success',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'message' => 'Database connection failed',
                'color' => 'danger',
            ];
        }
    }

    private function checkStorageUsage(): array
    {
        try {
            $storagePath = storage_path();
            $totalSpace = disk_total_space($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercent = round(($usedSpace / $totalSpace) * 100, 1);

            if ($usagePercent >= 90) {
                return [
                    'status' => 'critical',
                    'message' => "Storage {$usagePercent}% used - critical",
                    'color' => 'danger',
                    'usage_percent' => $usagePercent,
                ];
            } elseif ($usagePercent >= 75) {
                return [
                    'status' => 'warning',
                    'message' => "Storage {$usagePercent}% used - warning",
                    'color' => 'warning',
                    'usage_percent' => $usagePercent,
                ];
            }

            return [
                'status' => 'healthy',
                'message' => "Storage {$usagePercent}% used",
                'color' => 'success',
                'usage_percent' => $usagePercent,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'message' => 'Could not check storage',
                'color' => 'secondary',
            ];
        }
    }

    private function getRecentErrorCount(): array
    {
        try {
            $logPath = storage_path('logs/laravel.log');

            if (!file_exists($logPath)) {
                return [
                    'status' => 'healthy',
                    'message' => 'No errors in logs',
                    'color' => 'success',
                    'count' => 0,
                ];
            }

            $log = file_get_contents($logPath);
            $today = now()->format('Y-m-d');
            $pattern = "/\[{$today}.*?\].*?ERROR/";
            preg_match_all($pattern, $log, $matches);
            $errorCount = count($matches[0]);

            if ($errorCount >= 50) {
                return [
                    'status' => 'critical',
                    'message' => "{$errorCount} errors today - critical",
                    'color' => 'danger',
                    'count' => $errorCount,
                ];
            } elseif ($errorCount >= 10) {
                return [
                    'status' => 'warning',
                    'message' => "{$errorCount} errors today",
                    'color' => 'warning',
                    'count' => $errorCount,
                ];
            }

            return [
                'status' => 'healthy',
                'message' => $errorCount > 0 ? "{$errorCount} errors today" : 'No errors today',
                'color' => 'success',
                'count' => $errorCount,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'message' => 'Could not check error logs',
                'color' => 'secondary',
                'count' => 0,
            ];
        }
    }

    private function calculateOverallHealth(array $health): string
    {
        $statuses = [
            $health['database']['status'] ?? 'unknown',
            $health['storage']['status'] ?? 'unknown',
            $health['errors']['status'] ?? 'unknown',
        ];

        if (in_array('critical', $statuses)) {
            return 'critical';
        }
        if (in_array('warning', $statuses)) {
            return 'warning';
        }
        if (in_array('unknown', $statuses)) {
            return 'unknown';
        }

        return 'healthy';
    }

    public function getPendingCompanies()
    {
        return Company::where('status', Company::STATUS_PENDING)
            ->with('owner')
            ->latest()
            ->get();
    }

    // ─── Customer Growth & Engagement ────────────────────────────────

    public function getCustomerStats(): array
    {
        return Cache::remember('admin:customer_stats', 300, function () {
            $total = Customer::withoutGlobalScope('company')->count();
            $newWeek = Customer::withoutGlobalScope('company')->where('created_at', '>=', now()->subDays(7))->count();
            $newMonth = Customer::withoutGlobalScope('company')->where('created_at', '>=', now()->startOfMonth())->count();

            $topCompanies = DB::table('customers')
                ->join('companies', 'customers.company_id', '=', 'companies.id')
                ->select('companies.id', 'companies.name', DB::raw('COUNT(customers.id) as customer_count'))
                ->groupBy('companies.id', 'companies.name')
                ->orderByDesc('customer_count')
                ->limit(5)
                ->get();

            return [
                'total' => $total,
                'new_this_week' => $newWeek,
                'new_this_month' => $newMonth,
                'top_companies' => $topCompanies,
            ];
        });
    }

    public function getCustomerGrowthTrend(int $days = 30): array
    {
        $data = Customer::withoutGlobalScope('company')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $values[] = $data->get($date)?->count ?? 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getCustomerTransactionBreakdown(): array
    {
        $result = Transaction::withoutGlobalScope('company')
            ->where('status', 'completed')
            ->selectRaw('COUNT(CASE WHEN customer_id IS NOT NULL THEN 1 END) as registered')
            ->selectRaw('COUNT(CASE WHEN customer_id IS NULL THEN 1 END) as walkin')
            ->selectRaw('COUNT(*) as total')
            ->first();

        $total = $result->total ?: 1;
        return [
            'registered' => (int) $result->registered,
            'walkin' => (int) $result->walkin,
            'total' => (int) $result->total,
            'registered_pct' => round(($result->registered / $total) * 100, 1),
            'walkin_pct' => round(($result->walkin / $total) * 100, 1),
        ];
    }

    // ─── Product Intelligence ────────────────────────────────────────

    public function getTrendingProducts(int $limit = 10): array
    {
        return Cache::remember('admin:trending_products', 300, function () use ($limit) {
            return DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.status', 'completed')
                ->where('transactions.created_at', '>=', now()->subDays(30))
                ->select(
                    'transaction_items.product_name',
                    DB::raw('SUM(transaction_items.quantity) as total_sold'),
                    DB::raw('SUM(transaction_items.subtotal) as total_revenue'),
                    DB::raw('COUNT(DISTINCT transactions.company_id) as companies_selling')
                )
                ->groupBy('transaction_items.product_name')
                ->orderByDesc('total_sold')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public function getProductCatalogStats(): array
    {
        return [
            'total_products' => Product::withoutGlobalScope('company')->where('is_active', true)->count(),
            'companies_with_products' => DB::table('products')->where('is_active', true)->distinct('company_id')->count('company_id'),
            'avg_per_company' => (int) round(
                Product::withoutGlobalScope('company')->where('is_active', true)->count() /
                max(1, DB::table('products')->where('is_active', true)->distinct('company_id')->count('company_id'))
            ),
        ];
    }

    public function getLowStockAlerts(int $limit = 10): array
    {
        return DB::table('inventory')
            ->join('products', 'inventory.product_id', '=', 'products.id')
            ->join('companies', 'inventory.company_id', '=', 'companies.id')
            ->whereColumn('inventory.quantity', '<=', 'inventory.low_stock_threshold')
            ->where('products.is_active', true)
            ->select('companies.id', 'companies.name', DB::raw('COUNT(*) as low_stock_count'))
            ->groupBy('companies.id', 'companies.name')
            ->orderByDesc('low_stock_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // ─── Usage & Adoption Analytics ──────────────────────────────────

    public function getActiveCompanyStats(): array
    {
        $approvedCount = Company::where('status', Company::STATUS_APPROVED)->count();

        $activeToday = Transaction::withoutGlobalScope('company')
            ->whereDate('created_at', today())
            ->distinct()->count('company_id');

        $activeWeek = Transaction::withoutGlobalScope('company')
            ->where('created_at', '>=', now()->startOfWeek())
            ->distinct()->count('company_id');

        $churnRisk = Company::where('status', Company::STATUS_APPROVED)
            ->whereNotIn('id', function ($q) {
                $q->select('company_id')->from('transactions')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->distinct();
            })
            ->select('id', 'name', 'created_at')
            ->latest()
            ->limit(10)
            ->get();

        // Avg transactions per company per day (last 30 days)
        $totalTx30d = Transaction::withoutGlobalScope('company')
            ->where('created_at', '>=', now()->subDays(30))->count();
        $avgPerCompanyPerDay = $approvedCount > 0 ? round($totalTx30d / ($approvedCount * 30), 1) : 0;

        return [
            'active_today' => $activeToday,
            'active_this_week' => $activeWeek,
            'approved_total' => $approvedCount,
            'churn_risk' => $churnRisk,
            'avg_txn_per_company_per_day' => $avgPerCompanyPerDay,
        ];
    }

    public function getFeatureAdoptionRates(): array
    {
        $total = max(1, Company::where('status', Company::STATUS_APPROVED)->count());

        $features = [
            'expenses' => DB::table('expenses')->distinct('company_id')->count('company_id'),
            'orders' => DB::table('transactions')->where('type', 'order')->distinct('company_id')->count('company_id'),
            'whatsapp' => DB::table('whatsapp_receipt_logs')->distinct('company_id')->count('company_id'),
            'customers' => DB::table('customers')->distinct('company_id')->count('company_id'),
            'mobile_app' => MobileAppRequest::where('status', 'approved')->distinct('company_id')->count('company_id'),
            'branches' => Company::where('status', Company::STATUS_APPROVED)->where('branches_enabled', true)->count(),
        ];

        $result = [];
        foreach ($features as $name => $count) {
            $result[$name] = [
                'count' => $count,
                'pct' => round(($count / $total) * 100, 1),
            ];
        }

        return ['total_companies' => $total, 'features' => $result];
    }

    public function getActiveCompanyTrend(int $days = 14): array
    {
        $data = Transaction::withoutGlobalScope('company')
            ->where('created_at', '>=', now()->subDays($days))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(DISTINCT company_id) as active'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $values[] = (int) ($data->get($date)?->active ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    // ─── Customer Business Health ────────────────────────────────────

    public function getRevenuePerCompany(): array
    {
        $companies = Company::where('status', Company::STATUS_APPROVED)
            ->select('companies.id', 'companies.name')
            ->selectRaw('COALESCE((SELECT SUM(total) FROM transactions WHERE transactions.company_id = companies.id AND transactions.status = \'completed\'), 0) as total_revenue')
            ->orderByDesc('total_revenue')
            ->get();

        $totalRevenue = $companies->sum('total_revenue') ?: 1;
        $approvedCount = $companies->count() ?: 1;
        $top3Revenue = $companies->take(3)->sum('total_revenue');

        return [
            'avg_revenue' => round($totalRevenue / $approvedCount),
            'total_revenue' => $totalRevenue,
            'top3_concentration_pct' => round(($top3Revenue / $totalRevenue) * 100, 1),
            'top_companies' => $companies->take(10)->toArray(),
        ];
    }

    public function getDecliningCompanies(int $limit = 5): array
    {
        return DB::select("
            SELECT c.id, c.name,
                COALESCE(SUM(CASE WHEN t.created_at >= date('now', '-30 days') THEN t.total END), 0) as recent_revenue,
                COALESCE(SUM(CASE WHEN t.created_at >= date('now', '-60 days') AND t.created_at < date('now', '-30 days') THEN t.total END), 0) as previous_revenue
            FROM companies c
            LEFT JOIN transactions t ON c.id = t.company_id AND t.status = 'completed'
            WHERE c.status = 'approved'
            GROUP BY c.id, c.name
            HAVING previous_revenue > 0 AND recent_revenue < previous_revenue
            ORDER BY (previous_revenue - recent_revenue) DESC
            LIMIT ?
        ", [$limit]);
    }

    // ─── Mobile App Analytics ────────────────────────────────────────

    public function getMobileAppStats(): array
    {
        $totalDevices = MobileDevice::withoutGlobalScope('company')->count();
        $activeDevices = MobileDevice::withoutGlobalScope('company')->active()->count();

        $versionDistribution = MobileDevice::withoutGlobalScope('company')
            ->select('app_version', DB::raw('COUNT(*) as count'))
            ->groupBy('app_version')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $pendingRequests = MobileAppRequest::where('status', 'pending')
            ->with('company:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($r) => [
                'company_name' => $r->company?->name ?? 'Unknown',
                'requested_at' => $r->created_at->diffForHumans(),
                'days_waiting' => $r->created_at->diffInDays(now()),
            ]);

        $mobileUsers = User::whereIn('id', function ($q) {
            $q->select('user_id')->from('mobile_devices')->where('is_active', true)->distinct();
        })->count();

        return [
            'total_devices' => $totalDevices,
            'active_devices' => $activeDevices,
            'version_distribution' => $versionDistribution,
            'pending_requests' => $pendingRequests,
            'mobile_users' => $mobileUsers,
            'web_only_users' => User::where('role', '!=', 'platform_admin')->count() - $mobileUsers,
        ];
    }

    // ─── Retention & Satisfaction ────────────────────────────────────

    public function getSandukuSummary(): array
    {
        $byType = Sanduku::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type');
        $byStatus = Sanduku::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        $recent = Sanduku::latest()->limit(5)->get(['id', 'type', 'status', 'message', 'created_at']);

        return [
            'total' => Sanduku::count(),
            'by_type' => $byType->toArray(),
            'by_status' => $byStatus->toArray(),
            'recent' => $recent->toArray(),
        ];
    }

    public function getCompanyAgingDistribution(): array
    {
        return [
            'last_7d' => Company::where('status', Company::STATUS_APPROVED)->where('created_at', '>=', now()->subDays(7))->count(),
            'last_30d' => Company::where('status', Company::STATUS_APPROVED)->whereBetween('created_at', [now()->subDays(30), now()->subDays(7)])->count(),
            'last_90d' => Company::where('status', Company::STATUS_APPROVED)->whereBetween('created_at', [now()->subDays(90), now()->subDays(30)])->count(),
            'older' => Company::where('status', Company::STATUS_APPROVED)->where('created_at', '<', now()->subDays(90))->count(),
        ];
    }

    public function getOnboardingFunnel(): array
    {
        $completed = Company::where('onboarding_completed', true)->count();
        $steps = Company::where('onboarding_completed', false)
            ->selectRaw('onboarding_step, COUNT(*) as count')
            ->groupBy('onboarding_step')
            ->pluck('count', 'onboarding_step');

        return [
            'completed' => $completed,
            'step_1' => $steps->get(1, 0),
            'step_2' => $steps->get(2, 0),
            'step_3' => $steps->get(3, 0),
            'step_4' => $steps->get(4, 0),
        ];
    }
}
