<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $todaySales = Transaction::completed()->today()->sum('total');
        $todayTransactions = Transaction::completed()->today()->count();
        $totalProducts = Product::active()->count();
        $lowStockCount = Inventory::lowStock()->count();

        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(5)
            ->get();

        $topProducts = Product::withCount(['transactionItems as sold_count' => function ($query) {
            $query->whereHas('transaction', function ($q) {
                $q->completed()->whereDate('created_at', today());
            });
        }])
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

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
