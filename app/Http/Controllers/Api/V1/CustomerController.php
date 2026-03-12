<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::where('company_id', auth()->user()->company_id);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $perPage = min($request->input('per_page', 20), 100);
        $customers = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $customers->map(fn ($c) => $this->formatCustomer($c)),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1']);

        $customers = Customer::where('company_id', auth()->user()->company_id)
            ->active()
            ->search($request->input('q'))
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $customers->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'credit_limit' => (float) $c->credit_limit,
                'current_balance' => (float) $c->current_balance,
                'available_credit' => $c->available_credit,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers')->where('company_id', auth()->user()->company_id),
            ],
            'email' => 'nullable|email|max:255',
            'tin' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'tin' => $validated['tin'] ?? null,
            'address' => $validated['address'] ?? null,
            'credit_limit' => $validated['credit_limit'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Customer created successfully.',
            'data' => $this->formatCustomer($customer),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $recentTransactions = $customer->transactions()
            ->with('items')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $data = $this->formatCustomer($customer);
        $data['recent_transactions'] = $recentTransactions->map(fn ($t) => [
            'id' => $t->id,
            'transaction_number' => $t->transaction_number,
            'total' => (float) $t->total,
            'payment_method' => $t->payment_method,
            'status' => $t->status,
            'items_count' => $t->items->count(),
            'created_at' => $t->created_at->toIso8601String(),
        ]);

        return response()->json(['data' => $data]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('customers')->where('company_id', auth()->user()->company_id)->ignore($customer->id),
            ],
            'email' => 'nullable|email|max:255',
            'tin' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully.',
            'data' => $this->formatCustomer($customer),
        ]);
    }

    public function transactions(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $perPage = min($request->input('per_page', 20), 100);
        $transactions = $customer->transactions()
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $transactions->map(fn ($t) => [
                'id' => $t->id,
                'transaction_number' => $t->transaction_number,
                'type' => $t->type,
                'total' => (float) $t->total,
                'payment_method' => $t->payment_method,
                'payment_method_label' => $t->payment_method_label,
                'status' => $t->status,
                'items_count' => $t->items->count(),
                'created_at' => $t->created_at->toIso8601String(),
                'created_at_human' => $t->created_at->diffForHumans(),
            ]),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    public function creditHistory(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $perPage = min($request->input('per_page', 20), 100);
        $credits = $customer->creditTransactions()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $credits->map(fn ($c) => [
                'id' => $c->id,
                'type' => $c->type,
                'type_label' => $c->type_label,
                'amount' => (float) $c->amount,
                'balance_before' => (float) $c->balance_before,
                'balance_after' => (float) $c->balance_after,
                'payment_method' => $c->payment_method,
                'reference' => $c->reference,
                'notes' => $c->notes,
                'user' => [
                    'id' => $c->user->id,
                    'name' => $c->user->name,
                ],
                'transaction_id' => $c->transaction_id,
                'created_at' => $c->created_at->toIso8601String(),
                'created_at_human' => $c->created_at->diffForHumans(),
            ]),
            'meta' => [
                'current_page' => $credits->currentPage(),
                'last_page' => $credits->lastPage(),
                'per_page' => $credits->perPage(),
                'total' => $credits->total(),
            ],
        ]);
    }

    public function creditPayment(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,mobile,bank_transfer',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        if ((float) $customer->current_balance <= 0) {
            return response()->json([
                'message' => 'Customer has no outstanding balance.',
            ], 422);
        }

        try {
            $creditTransaction = DB::transaction(function () use ($customer, $validated) {
                // Lock the customer row for atomic update
                $customer = Customer::lockForUpdate()->findOrFail($customer->id);

                $balanceBefore = (float) $customer->current_balance;
                $amount = min($validated['amount'], $balanceBefore);
                $balanceAfter = $balanceBefore - $amount;

                $creditTransaction = CustomerCreditTransaction::create([
                    'customer_id' => $customer->id,
                    'type' => 'payment',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'payment_method' => $validated['payment_method'],
                    'reference' => $validated['reference'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => auth()->id(),
                ]);

                $customer->update(['current_balance' => $balanceAfter]);

                return $creditTransaction;
            });

            $customer->refresh();

            return response()->json([
                'message' => 'Payment recorded successfully.',
                'data' => [
                    'credit_transaction_id' => $creditTransaction->id,
                    'amount_paid' => (float) $creditTransaction->amount,
                    'balance_before' => (float) $creditTransaction->balance_before,
                    'balance_after' => (float) $creditTransaction->balance_after,
                    'customer' => $this->formatCustomer($customer),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function creditAdjustment(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCompanyOwner()) {
            return response()->json([
                'message' => 'Only the business owner can make credit adjustments.',
            ], 403);
        }

        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric',
            'notes' => 'required|string|max:500',
        ]);

        try {
            $creditTransaction = DB::transaction(function () use ($customer, $validated) {
                $customer = Customer::lockForUpdate()->findOrFail($customer->id);

                $balanceBefore = (float) $customer->current_balance;
                $balanceAfter = $balanceBefore + $validated['amount'];

                $creditTransaction = CustomerCreditTransaction::create([
                    'customer_id' => $customer->id,
                    'type' => 'adjustment',
                    'amount' => $validated['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'notes' => $validated['notes'],
                    'user_id' => auth()->id(),
                ]);

                $customer->update(['current_balance' => $balanceAfter]);

                return $creditTransaction;
            });

            $customer->refresh();

            return response()->json([
                'message' => 'Credit adjustment recorded.',
                'data' => [
                    'credit_transaction_id' => $creditTransaction->id,
                    'amount' => (float) $creditTransaction->amount,
                    'balance_before' => (float) $creditTransaction->balance_before,
                    'balance_after' => (float) $creditTransaction->balance_after,
                    'customer' => $this->formatCustomer($customer),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function formatCustomer(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'tin' => $customer->tin,
            'address' => $customer->address,
            'credit_limit' => (float) $customer->credit_limit,
            'current_balance' => (float) $customer->current_balance,
            'available_credit' => $customer->available_credit,
            'notes' => $customer->notes,
            'is_active' => $customer->is_active,
            'created_at' => $customer->created_at->toIso8601String(),
        ];
    }
}
