<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Sales Report</span>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.sales.pdf', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-pdf me-1"></i>PDF
                </a>
                <a href="{{ route('reports.sales.csv', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary date-preset" data-preset="today">Today</button>
                <button type="button" class="btn btn-sm btn-outline-primary date-preset" data-preset="week">This Week</button>
                <button type="button" class="btn btn-sm btn-outline-primary date-preset" data-preset="month">This Month</button>
                <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-preset="custom">Custom</button>
            </div>
            <form id="dateFilterForm" action="{{ route('reports.sales') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label mb-0 small">From</label>
                    <input type="date" class="form-control" name="date_from" id="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0 small">To</label>
                    <input type="date" class="form-control" name="date_to" id="date_to" value="{{ $dateTo }}">
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
    @push('scripts')
    <script>
        document.querySelectorAll('.date-preset').forEach(btn => {
            btn.addEventListener('click', function() {
                const preset = this.dataset.preset;
                const today = new Date();
                const form = document.getElementById('dateFilterForm');
                const dateFrom = document.getElementById('date_from');
                const dateTo = document.getElementById('date_to');
                const fmt = d => d.toISOString().split('T')[0];

                if (preset === 'today') {
                    dateFrom.value = fmt(today);
                    dateTo.value = fmt(today);
                    form.submit();
                } else if (preset === 'week') {
                    const monday = new Date(today);
                    monday.setDate(today.getDate() - ((today.getDay() + 6) % 7));
                    dateFrom.value = fmt(monday);
                    dateTo.value = fmt(today);
                    form.submit();
                } else if (preset === 'month') {
                    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                    dateFrom.value = fmt(firstDay);
                    dateTo.value = fmt(today);
                    form.submit();
                } else {
                    dateFrom.focus();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
