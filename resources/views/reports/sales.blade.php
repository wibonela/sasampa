<x-app-layout>
    <x-slot name="header">Sales Report</x-slot>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <form action="{{ route('reports.sales') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label mb-0 small">From</label>
                    <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0 small">To</label>
                    <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0 small">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small">Total Sales</div>
                    <div class="h3 mb-0">TZS {{ number_format($totalSales, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small">Total Transactions</div>
                    <div class="h3 mb-0">{{ $totalTransactions }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small">Average Transaction</div>
                    <div class="h3 mb-0">TZS {{ number_format($averageTransaction, 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Daily Sales -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Daily Sales</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transactions</th>
                                    <th class="text-end">Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salesData as $day)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                                        <td>{{ $day->count }}</td>
                                        <td class="text-end">TZS {{ number_format($day->total, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            No sales data for this period
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Payment Methods</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>Count</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentBreakdown as $method)
                                    <tr>
                                        <td>{{ ucfirst($method->payment_method) }}</td>
                                        <td>{{ $method->count }}</td>
                                        <td class="text-end">TZS {{ number_format($method->total, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            No data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
