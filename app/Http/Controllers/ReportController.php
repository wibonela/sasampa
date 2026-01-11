<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function sales(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $salesData = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalSales = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->sum('total');

        $totalTransactions = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->count();

        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        $paymentBreakdown = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->get();

        return view('reports.sales', compact(
            'salesData',
            'totalSales',
            'totalTransactions',
            'averageTransaction',
            'paymentBreakdown',
            'dateFrom',
            'dateTo'
        ));
    }

    public function products(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $topProducts = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('transaction_items.product_id, transaction_items.product_name, SUM(transaction_items.quantity) as total_quantity, SUM(transaction_items.subtotal) as total_revenue')
            ->groupBy('transaction_items.product_id', 'transaction_items.product_name')
            ->orderByDesc('total_revenue')
            ->take(20)
            ->get();

        return view('reports.products', compact('topProducts', 'dateFrom', 'dateTo'));
    }

    public function inventory(): View
    {
        $inventory = Product::with(['inventory', 'category'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'category' => $product->category?->name ?? 'Uncategorized',
                    'sku' => $product->sku,
                    'quantity' => $product->stock_quantity,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'stock_value' => $product->stock_quantity * $product->cost_price,
                    'is_low_stock' => $product->isLowStock(),
                ];
            });

        $totalValue = $inventory->sum('stock_value');
        $totalItems = $inventory->sum('quantity');
        $lowStockCount = $inventory->where('is_low_stock', true)->count();

        return view('reports.inventory', compact('inventory', 'totalValue', 'totalItems', 'lowStockCount'));
    }
}
