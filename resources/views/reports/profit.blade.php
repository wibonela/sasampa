<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Profit Report</h1>
                <p class="page-subtitle">Sales - Expenses = Profit</p>
            </div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Reports
            </a>
        </div>

        <!-- Date Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('reports.profit') }}" method="GET" class="row g-3 align-items-end">
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
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(0, 122, 255, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-graph-up-arrow" style="font-size: 20px; color: var(--apple-blue);"></i>
                        </div>
                        <h4 class="mb-1">TZS {{ number_format($totalSales) }}</h4>
                        <p class="text-secondary mb-0 small">Total Sales</p>
                        <span class="badge bg-secondary mt-1">{{ $totalTransactions }} transactions</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 59, 48, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-wallet2" style="font-size: 20px; color: var(--apple-red);"></i>
                        </div>
                        <h4 class="mb-1">TZS {{ number_format($totalExpenses) }}</h4>
                        <p class="text-secondary mb-0 small">Total Expenses</p>
                        <span class="badge bg-secondary mt-1">{{ $totalExpenseRecords }} records</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 {{ $netProfit >= 0 ? 'border-success' : 'border-danger' }}">
                    <div class="card-body text-center py-4">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $netProfit >= 0 ? 'rgba(52, 199, 89, 0.1)' : 'rgba(255, 59, 48, 0.1)' }}; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi {{ $netProfit >= 0 ? 'bi-arrow-up-circle' : 'bi-arrow-down-circle' }}" style="font-size: 20px; color: {{ $netProfit >= 0 ? 'var(--apple-green)' : 'var(--apple-red)' }};"></i>
                        </div>
                        <h4 class="mb-1 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">TZS {{ number_format($netProfit) }}</h4>
                        <p class="text-secondary mb-0 small">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 149, 0, 0.1); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-percent" style="font-size: 20px; color: var(--apple-orange);"></i>
                        </div>
                        <h4 class="mb-1 {{ $profitMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($profitMargin, 1) }}%</h4>
                        <p class="text-secondary mb-0 small">Profit Margin</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Daily Profit/Loss -->
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Daily Profit/Loss</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Sales</th>
                                    <th class="text-end">Expenses</th>
                                    <th class="text-end">Profit/Loss</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyProfit as $day)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($day['date'])->format('D, d M Y') }}</td>
                                        <td class="text-end text-primary">TZS {{ number_format($day['sales']) }}</td>
                                        <td class="text-end text-danger">TZS {{ number_format($day['expenses']) }}</td>
                                        <td class="text-end {{ $day['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>TZS {{ number_format($day['profit']) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">
                                            No data available for the selected period
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($dailyProfit->count() > 0)
                                <tfoot>
                                    <tr class="table-light">
                                        <th>Total</th>
                                        <th class="text-end text-primary">TZS {{ number_format($dailyProfit->sum('sales')) }}</th>
                                        <th class="text-end text-danger">TZS {{ number_format($dailyProfit->sum('expenses')) }}</th>
                                        <th class="text-end {{ $dailyProfit->sum('profit') >= 0 ? 'text-success' : 'text-danger' }}">
                                            TZS {{ number_format($dailyProfit->sum('profit')) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Expenses by Category -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Expense Categories</h5>
                    </div>
                    <div class="card-body">
                        @forelse($expensesByCategory as $category)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(255, 149, 0, 0.1); display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-folder" style="font-size: 12px; color: var(--apple-orange);"></i>
                                    </div>
                                    <span class="small">{{ $category->category_name }}</span>
                                </div>
                                <strong class="small">TZS {{ number_format($category->total) }}</strong>
                            </div>
                            @php
                                $percentage = $totalExpenses > 0 ? ($category->total / $totalExpenses) * 100 : 0;
                            @endphp
                            <div class="progress mb-3" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                            </div>
                        @empty
                            <div class="text-center text-secondary py-4">
                                <i class="bi bi-folder" style="font-size: 32px;"></i>
                                <p class="mt-2 mb-0 small">No expenses recorded</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit Summary -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-2">Profit Summary</h5>
                        <p class="text-secondary mb-0">
                            For the period {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="d-inline-block text-start p-3 rounded" style="background: {{ $netProfit >= 0 ? 'rgba(52, 199, 89, 0.1)' : 'rgba(255, 59, 48, 0.1)' }};">
                            <small class="text-secondary d-block">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</small>
                            <h3 class="mb-0 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">TZS {{ number_format(abs($netProfit)) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
