<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\CacheService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->currentBranch()?->id;

        // Get cached dashboard stats
        $stats = $this->cacheService->dashboardStats($companyId, $branchId);

        // Get cached top products
        $topProductsData = $this->cacheService->topProducts($companyId, $branchId);

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

        // Convert to collections for view
        $recentTransactions = collect($recentTransactionsData)->map(function ($item) {
            return (object) [
                'id' => $item['id'],
                'transaction_number' => $item['transaction_number'],
                'total' => $item['total'],
                'status' => $item['status'],
                'created_at' => \Carbon\Carbon::parse($item['created_at']),
                'user' => (object) ($item['user'] ?? ['name' => 'Unknown']),
            ];
        });

        $topProducts = collect($topProductsData)->map(function ($item) {
            return (object) [
                'id' => $item['id'],
                'name' => $item['name'],
                'selling_price' => $item['selling_price'],
                'sold_count' => $item['sold_today'] ?? 0,
            ];
        });

        return view('dashboard.index', compact(
            'todaySales',
            'todayTransactions',
            'totalProducts',
            'lowStockCount',
            'recentTransactions',
            'topProducts'
        ));
    }
}
