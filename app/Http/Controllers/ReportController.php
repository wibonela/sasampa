<?php

namespace App\Http\Controllers;

use App\Models\Expense;
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

    public function profit(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Total Sales
        $totalSales = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->sum('total');

        $totalTransactions = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->count();

        // Total Expenses
        $totalExpenses = Expense::inDateRange($dateFrom, $dateTo)
            ->selectRaw('SUM(amount * quantity) as total')
            ->value('total') ?? 0;

        $totalExpenseRecords = Expense::inDateRange($dateFrom, $dateTo)->count();

        // Calculate Profit
        $netProfit = $totalSales - $totalExpenses;
        $profitMargin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;

        // Daily Breakdown
        $dailySales = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, SUM(total) as sales')
            ->groupBy('date')
            ->pluck('sales', 'date')
            ->toArray();

        $dailyExpenses = Expense::inDateRange($dateFrom, $dateTo)
            ->selectRaw('DATE(expense_date) as date, SUM(amount * quantity) as expenses')
            ->groupBy('date')
            ->pluck('expenses', 'date')
            ->toArray();

        // Merge dates for daily profit
        $allDates = array_unique(array_merge(array_keys($dailySales), array_keys($dailyExpenses)));
        sort($allDates);

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

        // Expenses by Category
        $expensesByCategory = Expense::inDateRange($dateFrom, $dateTo)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category_name, SUM(expenses.amount * expenses.quantity) as total')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->get();

        return view('reports.profit', compact(
            'totalSales',
            'totalTransactions',
            'totalExpenses',
            'totalExpenseRecords',
            'netProfit',
            'profitMargin',
            'dailyProfit',
            'expensesByCategory',
            'dateFrom',
            'dateTo'
        ));
    }
}
