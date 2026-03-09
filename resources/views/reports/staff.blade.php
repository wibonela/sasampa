<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Staff Sales Report</h1>
                <p class="page-subtitle">Sales performance by staff member</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.staff.pdf', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'user_id' => $userId]) }}" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-pdf me-1"></i>PDF
                </a>
                <a href="{{ route('reports.staff.csv', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'user_id' => $userId]) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
                </a>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary date-preset" data-preset="today">Today</button>
                    <button type="button" class="btn btn-sm btn-outline-primary date-preset" data-preset="week">This Week</button>
                    <button type="button" class="btn btn-sm btn-outline-primary date-preset" data-preset="month">This Month</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-preset="custom">Custom</button>
                </div>
                <form id="dateFilterForm" action="{{ route('reports.staff') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Staff Member</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Staff</option>
                            @foreach($companyUsers as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: #fff; border: 1px solid var(--apple-border); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-graph-up-arrow" style="font-size: 20px; color: var(--apple-blue);"></i>
                        </div>
                        <h4 class="mb-1">TZS {{ number_format($totalSales) }}</h4>
                        <p class="text-secondary mb-0 small">Total Sales</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: #fff; border: 1px solid var(--apple-border); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-receipt" style="font-size: 20px; color: var(--apple-green);"></i>
                        </div>
                        <h4 class="mb-1">{{ $totalTransactions }}</h4>
                        <p class="text-secondary mb-0 small">Total Transactions</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: #fff; border: 1px solid var(--apple-border); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <i class="bi bi-people" style="font-size: 20px; color: #AF52DE;"></i>
                        </div>
                        <h4 class="mb-1">{{ $staffData->count() }}</h4>
                        <p class="text-secondary mb-0 small">Staff Members</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Sales Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Sales by Staff Member</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th class="text-end">Transactions</th>
                            <th class="text-end">Total Sales</th>
                            <th class="text-end">Avg Transaction</th>
                            <th class="text-end">% Share</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffData as $staff)
                            <tr>
                                <td>
                                    <strong>{{ $staff['name'] }}</strong>
                                    @if($staff['role'] === 'company_owner')
                                        <span class="badge bg-primary ms-1">Owner</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ $staff['transactions'] }}</td>
                                <td class="text-end">TZS {{ number_format($staff['total_sales']) }}</td>
                                <td class="text-end">TZS {{ number_format($staff['avg_transaction']) }}</td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $staff['share'] }}%"></div>
                                        </div>
                                        <span>{{ number_format($staff['share'], 1) }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('reports.staff', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'user_id' => $staff['id']]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary">
                                    No sales data for this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($staffData->count() > 0)
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th class="text-end">{{ $staffData->sum('transactions') }}</th>
                                <th class="text-end">TZS {{ number_format($staffData->sum('total_sales')) }}</th>
                                <th class="text-end">-</th>
                                <th class="text-end">100%</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Daily Breakdown for Selected Staff -->
        @if($dailyBreakdown && count($dailyBreakdown) > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Breakdown - {{ $selectedStaffName }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyBreakdown as $day)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($day->date)->format('D, d M Y') }}</td>
                                    <td class="text-end">{{ $day->count }}</td>
                                    <td class="text-end">TZS {{ number_format($day->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th class="text-end">{{ $dailyBreakdown->sum('count') }}</th>
                                <th class="text-end">TZS {{ number_format($dailyBreakdown->sum('total')) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
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
