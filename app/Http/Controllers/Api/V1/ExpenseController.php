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
                    'total' => (float) $expense->total,
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
            'total_amount' => $expenses->sum('total'),
            'total_count' => $expenses->count(),
            'by_category' => $expenses->groupBy('category.name')->map->sum('total'),
            'by_payment_method' => $expenses->groupBy('payment_method')->map->sum('total'),
        ];

        return response()->json([
            'data' => $expenses->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'amount' => (float) $expense->amount,
                    'quantity' => (float) $expense->quantity,
                    'total' => (float) $expense->total,
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
            'reference_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,mobile,bank',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['branch_id'] = auth()->user()->currentBranch?->id;

        $expense = Expense::create($validated);
        $expense->load(['category', 'user']);

        return response()->json([
            'message' => 'Expense recorded successfully.',
            'data' => [
                'id' => $expense->id,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'quantity' => (float) $expense->quantity,
                'total' => (float) $expense->total,
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
                'total' => (float) $expense->total,
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
            'reference_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'payment_method' => 'sometimes|in:cash,card,mobile,bank',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);
        $expense->load(['category', 'user']);

        return response()->json([
            'message' => 'Expense updated successfully.',
            'data' => [
                'id' => $expense->id,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'quantity' => (float) $expense->quantity,
                'total' => (float) $expense->total,
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

        // Total expenses by category
        $byCategory = Expense::inDateRange($dateFrom, $dateTo)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.id, expense_categories.name as category_name, SUM(expenses.amount * expenses.quantity) as total')
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

        return response()->json([
            'data' => [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'totals' => [
                    'amount' => (float) ($totalExpenses->total ?? 0),
                    'count' => (int) ($totalExpenses->count ?? 0),
                ],
                'by_category' => $byCategory->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->category_name,
                        'total' => (float) $item->total,
                    ];
                }),
                'by_payment_method' => $byPaymentMethod->map(function ($item) {
                    return [
                        'method' => $item->payment_method,
                        'total' => (float) $item->total,
                        'count' => (int) $item->count,
                    ];
                }),
                'daily' => $dailyExpenses->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'total' => (float) $item->total,
                        'count' => (int) $item->count,
                    ];
                }),
            ],
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
