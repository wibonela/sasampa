<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="page-title">Transactions</h1>
            <p class="page-subtitle">View all sales history</p>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form action="{{ route('transactions.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search"
                               placeholder="Transaction #" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="payment_method">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="mobile" {{ request('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="To">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Date</th>
                            <th>Cashier</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction) }}" style="text-decoration: none; color: var(--apple-blue); font-weight: 500;">
                                        {{ $transaction->transaction_number }}
                                    </a>
                                </td>
                                <td class="text-secondary">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $transaction->user->name }}</td>
                                <td class="text-secondary">{{ $transaction->customer_name ?? '—' }}</td>
                                <td>{{ $transaction->items->count() }}</td>
                                <td style="font-weight: 600;">{{ number_format($transaction->total, 0) }} TZS</td>
                                <td>
                                    @if($transaction->payment_method === 'cash')
                                        <span class="badge bg-success">Cash</span>
                                    @elseif($transaction->payment_method === 'card')
                                        <span class="badge bg-primary">Card</span>
                                    @else
                                        <span class="badge bg-secondary">Mobile</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($transaction->status === 'refunded')
                                        <span class="badge bg-warning">Refunded</span>
                                    @else
                                        <span class="badge bg-danger">Voided</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('transactions.show', $transaction) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('pos.receipt', $transaction) }}"
                                           class="btn btn-sm btn-outline-secondary" target="_blank" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="{{ route('pos.receipt.pdf', $transaction) }}"
                                           class="btn btn-sm btn-outline-success" title="Download PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-secondary">
                                    <i class="bi bi-receipt" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No transactions found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="card-footer">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
