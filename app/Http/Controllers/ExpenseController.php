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

        // Totals: line totals on this page (raw, not prorated). Prorated
        // figures live on the summary screen — the list view just shows
        // what was actually recorded.
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
        $validated = $this->validateExpense($request);

        $validated['user_id'] = auth()->id();
        $validated['branch_id'] = session('current_branch_id');

        Expense::create($this->withRecurrenceDefaults($validated));

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
        $validated = $this->validateExpense($request);

        $expense->update($this->withRecurrenceDefaults($validated));

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'expense_date' => 'required|date',
            'frequency' => 'required|in:' . implode(',', Expense::FREQUENCIES),
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'reference_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,mobile,bank',
            'notes' => 'nullable|string',
        ]);
    }

    /**
     * One-time expenses always use expense_date for both period bounds so
     * proration math has a non-null period to work with.
     */
    private function withRecurrenceDefaults(array $validated): array
    {
        if ($validated['frequency'] === Expense::FREQUENCY_ONE_TIME) {
            $validated['period_start'] = $validated['expense_date'];
            $validated['period_end'] = $validated['expense_date'];
        } else {
            $validated['period_start'] = $validated['period_start'] ?? $validated['expense_date'];
            // period_end nullable means "ongoing" — prorate to today.
        }
        return $validated;
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

        $from = \Illuminate\Support\Carbon::parse($dateFrom)->startOfDay();
        $to = \Illuminate\Support\Carbon::parse($dateTo)->endOfDay();

        // Pull every expense whose period overlaps the window once, then
        // prorate per expense in PHP. Cleaner than encoding proration in SQL
        // and SQLite-friendly.
        $expenses = Expense::with('category')->overlappingPeriod($from, $to)->get();

        $byCategoryMap = [];
        $byPaymentMap = [];
        $dailyMap = [];
        $totalAmount = 0.0;

        foreach ($expenses as $expense) {
            $allocated = $expense->proratedAmount($from, $to);
            if ($allocated <= 0) continue;

            $totalAmount += $allocated;

            $catName = $expense->category?->name ?? 'Uncategorized';
            $byCategoryMap[$catName] = ($byCategoryMap[$catName] ?? 0) + $allocated;

            $payMethod = $expense->payment_method;
            if (!isset($byPaymentMap[$payMethod])) {
                $byPaymentMap[$payMethod] = ['total' => 0.0, 'count' => 0];
            }
            $byPaymentMap[$payMethod]['total'] += $allocated;
            $byPaymentMap[$payMethod]['count'] += 1;
        }

        // Daily breakdown — recurring expenses contribute amount/period_days
        // to every day in their overlap with the window.
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $dayKey = $d->toDateString();
            $dayStart = $d->copy()->startOfDay();
            $dayEnd = $d->copy()->endOfDay();
            $dayTotal = 0.0;
            $dayCount = 0;
            foreach ($expenses as $expense) {
                $share = $expense->proratedAmount($dayStart, $dayEnd);
                if ($share > 0) {
                    $dayTotal += $share;
                    $dayCount += 1;
                }
            }
            if ($dayTotal > 0) {
                $dailyMap[$dayKey] = ['date' => $dayKey, 'total' => $dayTotal, 'count' => $dayCount];
            }
        }

        $byCategory = collect($byCategoryMap)
            ->map(fn ($total, $name) => (object) ['category_name' => $name, 'total' => round($total, 2)])
            ->values()
            ->sortByDesc('total')
            ->values();

        $byPaymentMethod = collect($byPaymentMap)
            ->map(fn ($v, $method) => (object) ['payment_method' => $method, 'total' => round($v['total'], 2), 'count' => $v['count']])
            ->values()
            ->sortByDesc('total')
            ->values();

        $dailyExpenses = collect(array_values($dailyMap))
            ->map(fn ($v) => (object) ['date' => $v['date'], 'total' => round($v['total'], 2), 'count' => $v['count']])
            ->values();

        $totalExpenses = (object) [
            'total' => round($totalAmount, 2),
            'count' => $expenses->count(),
        ];

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
