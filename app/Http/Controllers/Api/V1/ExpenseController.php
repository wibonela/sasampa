<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * List all expenses with filters
     */
    public function index(Request $request): JsonResponse
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
        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->input('category_id'));
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

        $perPage = min($request->input('per_page', 20), 100);
        $expenses = $query->orderByDesc('expense_date')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $expenses->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'amount' => (float) $expense->amount,
                    'quantity' => (float) $expense->quantity,
                    'total' => (float) $expense->line_total,
                    'unit' => $expense->unit,
                    'expense_date' => $expense->expense_date->format('Y-m-d'),
                    'expense_date_human' => $expense->expense_date->format('d M Y'),
                    'frequency' => $expense->frequency,
                    'period_start' => $expense->period_start?->format('Y-m-d'),
                    'period_end' => $expense->period_end?->format('Y-m-d'),
                    'is_recurring' => $expense->isRecurring(),
                    'payment_method' => $expense->payment_method,
                    'payment_method_label' => $expense->payment_method_label,
                    'supplier' => $expense->supplier,
                    'reference_number' => $expense->reference_number,
                    'notes' => $expense->notes,
                    'category' => [
                        'id' => $expense->category->id,
                        'name' => $expense->category->name,
                    ],
                    'user' => [
                        'id' => $expense->user->id,
                        'name' => $expense->user->name,
                    ],
                    'created_at' => $expense->created_at->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
        ]);
    }

    /**
     * Get today's expenses
     */
    public function today(): JsonResponse
    {
        $expenses = Expense::with(['category', 'user'])
            ->today()
            ->orderByDesc('created_at')
            ->get();

        $summary = [
            'total_amount' => $expenses->sum('line_total'),
            'total_count' => $expenses->count(),
            'by_category' => $expenses->groupBy('category.name')->map->sum('line_total'),
            'by_payment_method' => $expenses->groupBy('payment_method')->map->sum('line_total'),
        ];

        return response()->json([
            'data' => $expenses->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'amount' => (float) $expense->amount,
                    'quantity' => (float) $expense->quantity,
                    'total' => (float) $expense->line_total,
                    'unit' => $expense->unit,
                    'expense_date' => $expense->expense_date->format('Y-m-d'),
                    'payment_method' => $expense->payment_method,
                    'payment_method_label' => $expense->payment_method_label,
                    'category' => [
                        'id' => $expense->category->id,
                        'name' => $expense->category->name,
                    ],
                    'created_at' => $expense->created_at->toIso8601String(),
                    'created_at_human' => $expense->created_at->diffForHumans(),
                ];
            }),
            'summary' => $summary,
        ]);
    }

    /**
     * Get expense categories
     */
    public function categories(): JsonResponse
    {
        $categories = ExpenseCategory::active()
            ->withCount('expenses')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'expenses_count' => $category->expenses_count,
                ];
            }),
        ]);
    }

    /**
     * Create a new expense
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'expense_date' => 'required|date',
            'frequency' => 'nullable|in:' . implode(',', Expense::FREQUENCIES),
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'reference_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,mobile,bank',
            'notes' => 'nullable|string',
        ]);

        $validated['frequency'] = $validated['frequency'] ?? Expense::FREQUENCY_ONE_TIME;
        if ($validated['frequency'] === Expense::FREQUENCY_ONE_TIME) {
            $validated['period_start'] = $validated['expense_date'];
            $validated['period_end'] = $validated['expense_date'];
        } else {
            $validated['period_start'] = $validated['period_start'] ?? $validated['expense_date'];
        }

        $validated['user_id'] = auth()->id();
        $validated['company_id'] = auth()->user()->company_id;
        $validated['branch_id'] = auth()->user()->currentBranch()?->id ?? null;

        $expense = Expense::create($validated);
        $expense->load(['category', 'user']);

        return response()->json([
            'message' => 'Expense recorded successfully.',
            'data' => [
                'id' => $expense->id,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'quantity' => (float) $expense->quantity,
                'total' => (float) $expense->line_total,
                'unit' => $expense->unit,
                'expense_date' => $expense->expense_date->format('Y-m-d'),
                'payment_method' => $expense->payment_method,
                'payment_method_label' => $expense->payment_method_label,
                'supplier' => $expense->supplier,
                'category' => [
                    'id' => $expense->category->id,
                    'name' => $expense->category->name,
                ],
            ],
        ], 201);
    }

    /**
     * Get a single expense
     */
    public function show(int $id): JsonResponse
    {
        $expense = Expense::with(['category', 'user', 'branch'])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $expense->id,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'quantity' => (float) $expense->quantity,
                'total' => (float) $expense->line_total,
                'unit' => $expense->unit,
                'expense_date' => $expense->expense_date->format('Y-m-d'),
                'expense_date_human' => $expense->expense_date->format('d M Y'),
                'payment_method' => $expense->payment_method,
                'payment_method_label' => $expense->payment_method_label,
                'supplier' => $expense->supplier,
                'reference_number' => $expense->reference_number,
                'notes' => $expense->notes,
                'category' => [
                    'id' => $expense->category->id,
                    'name' => $expense->category->name,
                ],
                'user' => [
                    'id' => $expense->user->id,
                    'name' => $expense->user->name,
                ],
                'branch' => $expense->branch ? [
                    'id' => $expense->branch->id,
                    'name' => $expense->branch->name,
                ] : null,
                'created_at' => $expense->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update an expense
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'expense_category_id' => 'sometimes|exists:expense_categories,id',
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'expense_date' => 'sometimes|date',
            'frequency' => 'sometimes|in:' . implode(',', Expense::FREQUENCIES),
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'reference_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'payment_method' => 'sometimes|in:cash,card,mobile,bank',
            'notes' => 'nullable|string',
        ]);

        $frequency = $validated['frequency'] ?? $expense->frequency;
        $expenseDate = $validated['expense_date'] ?? $expense->expense_date->toDateString();
        if ($frequency === Expense::FREQUENCY_ONE_TIME) {
            $validated['period_start'] = $expenseDate;
            $validated['period_end'] = $expenseDate;
        }

        $expense->update($validated);
        $expense->load(['category', 'user']);

        return response()->json([
            'message' => 'Expense updated successfully.',
            'data' => [
                'id' => $expense->id,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'quantity' => (float) $expense->quantity,
                'total' => (float) $expense->line_total,
                'unit' => $expense->unit,
                'expense_date' => $expense->expense_date->format('Y-m-d'),
                'payment_method' => $expense->payment_method,
                'category' => [
                    'id' => $expense->category->id,
                    'name' => $expense->category->name,
                ],
            ],
        ]);
    }

    /**
     * Delete an expense
     */
    public function destroy(int $id): JsonResponse
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully.',
        ]);
    }

    /**
     * Get expense summary/analytics
     */
    public function summary(Request $request): JsonResponse
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $from = \Illuminate\Support\Carbon::parse($dateFrom)->startOfDay();
        $to = \Illuminate\Support\Carbon::parse($dateTo)->endOfDay();

        $expenses = Expense::with('category')->overlappingPeriod($from, $to)->get();

        $byCategoryMap = [];
        $byPaymentMap = [];
        $totalAmount = 0.0;
        foreach ($expenses as $expense) {
            $allocated = $expense->proratedAmount($from, $to);
            if ($allocated <= 0) continue;
            $totalAmount += $allocated;

            $catId = $expense->expense_category_id;
            if (!isset($byCategoryMap[$catId])) {
                $byCategoryMap[$catId] = [
                    'id' => $catId,
                    'name' => $expense->category?->name ?? 'Uncategorized',
                    'total' => 0.0,
                ];
            }
            $byCategoryMap[$catId]['total'] += $allocated;

            $method = $expense->payment_method;
            if (!isset($byPaymentMap[$method])) {
                $byPaymentMap[$method] = ['method' => $method, 'total' => 0.0, 'count' => 0];
            }
            $byPaymentMap[$method]['total'] += $allocated;
            $byPaymentMap[$method]['count'] += 1;
        }

        $daily = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
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
                $daily[] = ['date' => $d->toDateString(), 'total' => round($dayTotal, 2), 'count' => $dayCount];
            }
        }

        $byCategory = collect($byCategoryMap)->values()
            ->map(fn ($v) => ['id' => $v['id'], 'name' => $v['name'], 'total' => round($v['total'], 2)])
            ->sortByDesc('total')->values();
        $byPaymentMethod = collect($byPaymentMap)->values()
            ->map(fn ($v) => ['method' => $v['method'], 'total' => round($v['total'], 2), 'count' => $v['count']])
            ->sortByDesc('total')->values();

        return response()->json([
            'data' => [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'totals' => [
                    'amount' => round($totalAmount, 2),
                    'count' => $expenses->count(),
                ],
                'by_category' => $byCategory,
                'by_payment_method' => $byPaymentMethod,
                'daily' => $daily,
            ],
        ]);
    }

    /**
     * Get distinct suppliers from previous expenses
     */
    public function suppliers(Request $request): JsonResponse
    {
        $query = Expense::whereNotNull('supplier')
            ->where('supplier', '!=', '')
            ->select('supplier')
            ->distinct()
            ->orderBy('supplier');

        if ($request->filled('q')) {
            $query->where('supplier', 'like', '%' . $request->input('q') . '%');
        }

        $suppliers = $query->limit(20)->pluck('supplier');

        return response()->json([
            'data' => $suppliers,
        ]);
    }

    /**
     * Create a new expense category
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $category = ExpenseCategory::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
            ],
        ], 201);
    }
}
