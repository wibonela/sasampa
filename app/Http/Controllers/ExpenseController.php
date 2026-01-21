<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::with(['category', 'user', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('expense_category_id', $request->input('category'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->input('date_to'));
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        $expenses = $query->orderByDesc('expense_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategory::active()->orderBy('name')->get();

        // Calculate totals for filtered results
        $totalQuery = clone $query;
        $totals = $totalQuery->selectRaw('SUM(amount * quantity) as total_amount, COUNT(*) as total_count')
            ->reorder()
            ->first();

        return view('expenses.index', compact('expenses', 'categories', 'totals'));
    }

    public function create(): View
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'expense_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,mobile,bank',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['branch_id'] = session('current_branch_id');

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense): View
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'expense_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,mobile,bank',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function summary(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Total expenses by category
        $byCategory = Expense::inDateRange($dateFrom, $dateTo)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category_name, SUM(expenses.amount * expenses.quantity) as total')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->get();

        // Daily breakdown
        $dailyExpenses = Expense::inDateRange($dateFrom, $dateTo)
            ->selectRaw('DATE(expense_date) as date, SUM(amount * quantity) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Payment method breakdown
        $byPaymentMethod = Expense::inDateRange($dateFrom, $dateTo)
            ->selectRaw('payment_method, SUM(amount * quantity) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Total summary
        $totalExpenses = Expense::inDateRange($dateFrom, $dateTo)
            ->selectRaw('SUM(amount * quantity) as total, COUNT(*) as count')
            ->first();

        return view('expenses.summary', compact(
            'byCategory',
            'dailyExpenses',
            'byPaymentMethod',
            'totalExpenses',
            'dateFrom',
            'dateTo'
        ));
    }
}
