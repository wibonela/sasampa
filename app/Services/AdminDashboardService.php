<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
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
                $selectPeriod = DB::raw('YEARWEEK(created_at, 1) as period');
                $query->where('created_at', '>=', now()->subWeeks($limit));
                break;
            case 'monthly':
                $selectPeriod = DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period");
                $query->where('created_at', '>=', now()->subMonths($limit));
                break;
            default: // daily
                $selectPeriod = DB::raw('DATE(created_at) as period');
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
}
