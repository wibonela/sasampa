<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Expense Summary</h1>
                <p class="page-subtitle">Overview of your expenses</p>
            </div>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Expenses
            </a>
        </div>

        <!-- Date Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('expenses.summary') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(255, 59, 48, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-wallet2" style="font-size: 24px; color: var(--apple-red);"></i>
                        </div>
                        <h3 class="mb-1">TZS {{ number_format($totalExpenses->total ?? 0) }}</h3>
                        <p class="text-secondary mb-0">Total Expenses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(0, 122, 255, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-receipt" style="font-size: 24px; color: var(--apple-blue);"></i>
                        </div>
                        <h3 class="mb-1">{{ number_format($totalExpenses->count ?? 0) }}</h3>
                        <p class="text-secondary mb-0">Total Records</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(255, 149, 0, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-calculator" style="font-size: 24px; color: var(--apple-orange);"></i>
                        </div>
                        <h3 class="mb-1">TZS {{ number_format(($totalExpenses->total ?? 0) / max(1, $totalExpenses->count ?? 1)) }}</h3>
                        <p class="text-secondary mb-0">Average per Record</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Expenses by Category -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Expenses by Category</h5>
                    </div>
                    <div class="card-body">
                        @forelse($byCategory as $item)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255, 149, 0, 0.1); display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-folder" style="color: var(--apple-orange);"></i>
                                    </div>
                                    <span>{{ $item->category_name }}</span>
                                </div>
                                <strong>TZS {{ number_format($item->total) }}</strong>
                            </div>
                            @php
                                $percentage = ($totalExpenses->total ?? 0) > 0 ? ($item->total / $totalExpenses->total) * 100 : 0;
                            @endphp
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                            </div>
                        @empty
                            <div class="text-center text-secondary py-4">
                                <i class="bi bi-folder" style="font-size: 32px;"></i>
                                <p class="mt-2 mb-0">No data available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Payment Method Breakdown -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Methods</h5>
                    </div>
                    <div class="card-body">
                        @forelse($byPaymentMethod as $item)
                            @php
                                $label = match($item->payment_method) {
                                    'cash' => 'Cash',
                                    'mobile' => 'Mobile Money',
                                    'card' => 'Card',
                                    'bank' => 'Bank Transfer',
                                    default => ucfirst($item->payment_method)
                                };
                                $color = match($item->payment_method) {
                                    'cash' => 'success',
                                    'mobile' => 'info',
                                    'card' => 'primary',
                                    'bank' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                    <span class="text-secondary small">({{ $item->count }} records)</span>
                                </div>
                                <strong>TZS {{ number_format($item->total) }}</strong>
                            </div>
                        @empty
                            <div class="text-center text-secondary py-4">
                                <i class="bi bi-credit-card" style="font-size: 32px;"></i>
                                <p class="mt-2 mb-0">No data available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Expenses Table -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Daily Breakdown</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Records</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyExpenses as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->date)->format('D, d M Y') }}</td>
                                <td>{{ $day->count }}</td>
                                <td><strong>TZS {{ number_format($day->total) }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-secondary">
                                    No expenses recorded in this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($dailyExpenses->count() > 0)
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th>{{ $dailyExpenses->sum('count') }}</th>
                                <th>TZS {{ number_format($dailyExpenses->sum('total')) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
