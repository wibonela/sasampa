<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    // ─── Sales Report ───────────────────────────────────────────────

    private function getSalesData(Request $request): array
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

        return compact('salesData', 'totalSales', 'totalTransactions', 'averageTransaction', 'paymentBreakdown', 'dateFrom', 'dateTo');
    }

    public function sales(Request $request): View
    {
        return view('reports.sales', $this->getSalesData($request));
    }

    public function salesPdf(Request $request): Response
    {
        $data = $this->getSalesData($request);
        $data['companyName'] = auth()->user()->company->name;

        $pdf = Pdf::loadView('reports.pdf.sales', $data);
        return $pdf->download('sales-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.pdf');
    }

    public function salesCsv(Request $request): StreamedResponse
    {
        $data = $this->getSalesData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Sales Report: ' . $data['dateFrom'] . ' to ' . $data['dateTo']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Date', 'Transactions', 'Total Sales (TZS)']);
            foreach ($data['salesData'] as $day) {
                fputcsv($handle, [$day->date, $day->count, $day->total]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Total Sales', $data['totalSales']]);
            fputcsv($handle, ['Total Transactions', $data['totalTransactions']]);
            fputcsv($handle, ['Average Transaction', round($data['averageTransaction'])]);
            fputcsv($handle, []);
            fputcsv($handle, ['Payment Method', 'Count', 'Total (TZS)']);
            foreach ($data['paymentBreakdown'] as $method) {
                fputcsv($handle, [ucfirst($method->payment_method), $method->count, $method->total]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.csv"',
        ]);
    }

    // ─── Products Report ────────────────────────────────────────────

    private function getProductsData(Request $request): array
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

        return compact('topProducts', 'dateFrom', 'dateTo');
    }

    public function products(Request $request): View
    {
        return view('reports.products', $this->getProductsData($request));
    }

    public function productsPdf(Request $request): Response
    {
        $data = $this->getProductsData($request);
        $data['companyName'] = auth()->user()->company->name;

        $pdf = Pdf::loadView('reports.pdf.products', $data);
        return $pdf->download('products-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.pdf');
    }

    public function productsCsv(Request $request): StreamedResponse
    {
        $data = $this->getProductsData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Product Performance Report: ' . $data['dateFrom'] . ' to ' . $data['dateTo']]);
            fputcsv($handle, []);
            fputcsv($handle, ['#', 'Product', 'Quantity Sold', 'Revenue (TZS)']);
            foreach ($data['topProducts'] as $index => $product) {
                fputcsv($handle, [$index + 1, $product->product_name, $product->total_quantity, $product->total_revenue]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.csv"',
        ]);
    }

    // ─── Inventory Report ───────────────────────────────────────────

    private function getInventoryData(): array
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

        return compact('inventory', 'totalValue', 'totalItems', 'lowStockCount');
    }

    public function inventory(): View
    {
        return view('reports.inventory', $this->getInventoryData());
    }

    public function inventoryPdf(): Response
    {
        $data = $this->getInventoryData();
        $data['companyName'] = auth()->user()->company->name;

        $pdf = Pdf::loadView('reports.pdf.inventory', $data);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function inventoryCsv(): StreamedResponse
    {
        $data = $this->getInventoryData();

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Inventory Report - ' . now()->format('Y-m-d')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Product', 'Category', 'SKU', 'Quantity', 'Cost Price (TZS)', 'Selling Price (TZS)', 'Stock Value (TZS)', 'Status']);
            foreach ($data['inventory'] as $item) {
                $status = $item['quantity'] == 0 ? 'Out of Stock' : ($item['is_low_stock'] ? 'Low Stock' : 'OK');
                fputcsv($handle, [
                    $item['name'], $item['category'], $item['sku'] ?? '-',
                    $item['quantity'], $item['cost_price'], $item['selling_price'],
                    $item['stock_value'], $status,
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Total Stock Value', $data['totalValue']]);
            fputcsv($handle, ['Total Items', $data['totalItems']]);
            fputcsv($handle, ['Low Stock Items', $data['lowStockCount']]);
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory-report-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ─── Profit Report ──────────────────────────────────────────────

    private function getProfitData(Request $request): array
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $totalSales = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->sum('total');

        $totalTransactions = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->count();

        $totalExpenses = Expense::inDateRange($dateFrom, $dateTo)
            ->selectRaw('SUM(amount * quantity) as total')
            ->value('total') ?? 0;

        $totalExpenseRecords = Expense::inDateRange($dateFrom, $dateTo)->count();

        $netProfit = $totalSales - $totalExpenses;
        $profitMargin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;

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

        $expensesByCategory = Expense::inDateRange($dateFrom, $dateTo)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category_name, SUM(expenses.amount * expenses.quantity) as total')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->get();

        return compact(
            'totalSales', 'totalTransactions', 'totalExpenses', 'totalExpenseRecords',
            'netProfit', 'profitMargin', 'dailyProfit', 'expensesByCategory', 'dateFrom', 'dateTo'
        );
    }

    public function profit(Request $request): View
    {
        return view('reports.profit', $this->getProfitData($request));
    }

    public function profitPdf(Request $request): Response
    {
        $data = $this->getProfitData($request);
        $data['companyName'] = auth()->user()->company->name;

        $pdf = Pdf::loadView('reports.pdf.profit', $data);
        return $pdf->download('profit-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.pdf');
    }

    public function profitCsv(Request $request): StreamedResponse
    {
        $data = $this->getProfitData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Profit Report: ' . $data['dateFrom'] . ' to ' . $data['dateTo']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Total Sales (TZS)', $data['totalSales']]);
            fputcsv($handle, ['Total Expenses (TZS)', $data['totalExpenses']]);
            fputcsv($handle, ['Net Profit (TZS)', $data['netProfit']]);
            fputcsv($handle, ['Profit Margin (%)', round($data['profitMargin'], 1)]);
            fputcsv($handle, []);
            fputcsv($handle, ['Date', 'Sales (TZS)', 'Expenses (TZS)', 'Profit/Loss (TZS)']);
            foreach ($data['dailyProfit'] as $day) {
                fputcsv($handle, [$day['date'], $day['sales'], $day['expenses'], $day['profit']]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Expense Category', 'Amount (TZS)']);
            foreach ($data['expensesByCategory'] as $category) {
                fputcsv($handle, [$category->category_name, $category->total]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="profit-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.csv"',
        ]);
    }

    // ─── Staff Sales Report (Admin/Owner only) ─────────────────────

    private function getStaffData(Request $request): array
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $userId = $request->input('user_id');

        $companyId = auth()->user()->company_id;

        // Get all company users (cashiers + owner)
        $companyUsers = User::where('company_id', $companyId)
            ->whereIn('role', [User::ROLE_CASHIER, User::ROLE_COMPANY_OWNER])
            ->orderBy('name')
            ->get();

        $totalSales = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->sum('total');

        $totalTransactions = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->count();

        // Per-staff breakdown
        $staffData = $companyUsers->map(function ($user) use ($dateFrom, $dateTo, $totalSales) {
            $userSales = Transaction::completed()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('total');

            $userTransactions = Transaction::completed()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'transactions' => $userTransactions,
                'total_sales' => $userSales,
                'avg_transaction' => $userTransactions > 0 ? $userSales / $userTransactions : 0,
                'share' => $totalSales > 0 ? ($userSales / $totalSales) * 100 : 0,
            ];
        })->sortByDesc('total_sales')->values();

        // Daily breakdown for selected cashier
        $dailyBreakdown = null;
        $selectedStaffName = null;
        if ($userId) {
            $selectedUser = $companyUsers->firstWhere('id', $userId);
            $selectedStaffName = $selectedUser?->name;

            if ($selectedUser) {
                $dailyBreakdown = Transaction::completed()
                    ->where('user_id', $userId)
                    ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
            }
        }

        return compact(
            'staffData', 'companyUsers', 'totalSales', 'totalTransactions',
            'dailyBreakdown', 'selectedStaffName', 'dateFrom', 'dateTo', 'userId'
        );
    }

    public function staff(Request $request): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('reports.staff', $this->getStaffData($request));
    }

    public function staffPdf(Request $request): Response
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $this->getStaffData($request);
        $data['companyName'] = auth()->user()->company->name;

        $pdf = Pdf::loadView('reports.pdf.staff', $data);
        return $pdf->download('staff-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.pdf');
    }

    public function staffCsv(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $this->getStaffData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Staff Sales Report: ' . $data['dateFrom'] . ' to ' . $data['dateTo']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Staff Name', 'Transactions', 'Total Sales (TZS)', 'Avg Transaction (TZS)', '% Share']);
            foreach ($data['staffData'] as $staff) {
                fputcsv($handle, [
                    $staff['name'], $staff['transactions'], $staff['total_sales'],
                    round($staff['avg_transaction']), round($staff['share'], 1),
                ]);
            }
            if ($data['dailyBreakdown'] && count($data['dailyBreakdown']) > 0) {
                fputcsv($handle, []);
                fputcsv($handle, ['Daily Breakdown - ' . $data['selectedStaffName']]);
                fputcsv($handle, ['Date', 'Transactions', 'Total Sales (TZS)']);
                foreach ($data['dailyBreakdown'] as $day) {
                    fputcsv($handle, [$day->date, $day->count, $day->total]);
                }
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="staff-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.csv"',
        ]);
    }
}
