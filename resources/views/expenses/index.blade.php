<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Matumizi (Expenses)</h1>
                <p class="page-subtitle">Track operational costs and raw materials</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('expenses.summary') }}" class="btn btn-outline-primary">
                    <i class="bi bi-pie-chart me-1"></i>Summary
                </a>
                <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Add Expense
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3">
                        <div class="text-secondary small">Total Expenses</div>
                        <div class="h5 mb-0 mt-1">TZS {{ number_format($totals->total_amount ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3">
                        <div class="text-secondary small">Records</div>
                        <div class="h5 mb-0 mt-1">{{ number_format($totals->total_count ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('expenses.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search description, supplier..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="payment_method" class="form-select">
                            <option value="">All Payments</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="mobile" {{ request('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>
                                    <div style="font-weight: 500;">{{ $expense->expense_date->format('d M Y') }}</div>
                                    <small class="text-secondary">{{ $expense->user->name }}</small>
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ $expense->description }}</div>
                                    @if($expense->supplier)
                                        <small class="text-secondary">{{ $expense->supplier }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $expense->category->name }}</span>
                                </td>
                                <td>
                                    {{ number_format($expense->quantity, 2) }}
                                    @if($expense->unit)
                                        <small class="text-secondary">{{ $expense->unit }}</small>
                                    @endif
                                </td>
                                <td>TZS {{ number_format($expense->amount) }}</td>
                                <td style="font-weight: 600;">TZS {{ number_format($expense->total) }}</td>
                                <td>
                                    <span class="badge bg-{{ $expense->payment_method == 'cash' ? 'success' : ($expense->payment_method == 'mobile' ? 'info' : 'primary') }}">
                                        {{ $expense->payment_method_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                                              onsubmit="return confirm('Delete this expense?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-secondary">
                                    <i class="bi bi-wallet2" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No expenses recorded</p>
                                    <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm mt-2">Add First Expense</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())
                <div class="card-footer">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
