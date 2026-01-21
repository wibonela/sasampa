<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Profit Trends</h1>
                <p class="page-subtitle">Monthly and yearly performance analysis</p>
            </div>
            <a href="{{ route('analytics.profit') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Analytics
            </a>
        </div>

        <!-- Branch Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('analytics.profit.trends') }}" method="GET" class="row g-3 align-items-end">
                    @if($branches->count() > 0)
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>
                        <select name="branch" class="form-select">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Year over Year Comparison -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $yearComparison['current']['year'] }} (Current Year)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-secondary small">Sales</div>
                                <h5 class="text-primary mb-0">{{ number_format($yearComparison['current']['sales'] / 1000000, 1) }}M</h5>
                            </div>
                            <div class="col-4">
                                <div class="text-secondary small">Expenses</div>
                                <h5 class="text-danger mb-0">{{ number_format($yearComparison['current']['expenses'] / 1000000, 1) }}M</h5>
                            </div>
                            <div class="col-4">
                                <div class="text-secondary small">Profit</div>
                                <h5 class="{{ $yearComparison['current']['profit'] >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    {{ number_format($yearComparison['current']['profit'] / 1000000, 1) }}M
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $yearComparison['last']['year'] }} (Previous Year)</h5>
                        @if($yearComparison['growth'] != 0)
                            <span class="badge {{ $yearComparison['growth'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                <i class="bi {{ $yearComparison['growth'] >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                                {{ number_format(abs($yearComparison['growth']), 1) }}% YoY
                            </span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-secondary small">Sales</div>
                                <h5 class="text-primary mb-0">{{ number_format($yearComparison['last']['sales'] / 1000000, 1) }}M</h5>
                            </div>
                            <div class="col-4">
                                <div class="text-secondary small">Expenses</div>
                                <h5 class="text-danger mb-0">{{ number_format($yearComparison['last']['expenses'] / 1000000, 1) }}M</h5>
                            </div>
                            <div class="col-4">
                                <div class="text-secondary small">Profit</div>
                                <h5 class="{{ $yearComparison['last']['profit'] >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    {{ number_format($yearComparison['last']['profit'] / 1000000, 1) }}M
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small">Avg Monthly Profit</div>
                        <h4 class="mb-0 {{ $avgMonthlyProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            TZS {{ number_format($avgMonthlyProfit) }}
                        </h4>
                    </div>
                </div>
            </div>
            @if($bestMonth)
            <div class="col-6 col-md-3">
                <div class="card border-success">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small">Best Month</div>
                        <h5 class="mb-0 text-success">{{ $bestMonth['month'] }}</h5>
                        <div class="small text-success">TZS {{ number_format($bestMonth['profit']) }}</div>
                    </div>
                </div>
            </div>
            @endif
            @if($worstMonth)
            <div class="col-6 col-md-3">
                <div class="card border-danger">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small">Worst Month</div>
                        <h5 class="mb-0 text-danger">{{ $worstMonth['month'] }}</h5>
                        <div class="small text-danger">TZS {{ number_format($worstMonth['profit']) }}</div>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small">Profitable Months</div>
                        <h4 class="mb-0">{{ $monthlyTrends->where('profit', '>', 0)->count() }} / 12</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Monthly Performance (Last 12 Months)</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Profit/Loss</th>
                            <th class="text-end">Margin</th>
                            <th style="width: 200px;">Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $maxProfit = $monthlyTrends->max('profit');
                            $minProfit = $monthlyTrends->min('profit');
                            $range = max(abs($maxProfit), abs($minProfit));
                        @endphp
                        @foreach($monthlyTrends as $month)
                            @php
                                $margin = $month['sales'] > 0 ? ($month['profit'] / $month['sales']) * 100 : 0;
                                $barWidth = $range > 0 ? (abs($month['profit']) / $range) * 100 : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $month['month'] }}</strong></td>
                                <td class="text-end">TZS {{ number_format($month['sales']) }}</td>
                                <td class="text-end text-danger">TZS {{ number_format($month['expenses']) }}</td>
                                <td class="text-end {{ $month['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <strong>TZS {{ number_format($month['profit']) }}</strong>
                                </td>
                                <td class="text-end {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($margin, 1) }}%
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar {{ $month['profit'] >= 0 ? 'bg-success' : 'bg-danger' }}"
                                                 style="width: {{ $barWidth }}%"></div>
                                        </div>
                                        @if($month['profit'] == $maxProfit && $month['profit'] > 0)
                                            <i class="bi bi-trophy-fill text-success" title="Best Month"></i>
                                        @elseif($month['profit'] == $minProfit && $month['profit'] < $maxProfit)
                                            <i class="bi bi-exclamation-triangle-fill text-danger" title="Worst Month"></i>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total (12 Months)</th>
                            <th class="text-end text-primary">TZS {{ number_format($monthlyTrends->sum('sales')) }}</th>
                            <th class="text-end text-danger">TZS {{ number_format($monthlyTrends->sum('expenses')) }}</th>
                            <th class="text-end {{ $monthlyTrends->sum('profit') >= 0 ? 'text-success' : 'text-danger' }}">
                                TZS {{ number_format($monthlyTrends->sum('profit')) }}
                            </th>
                            <th class="text-end">
                                @php $totalMargin = $monthlyTrends->sum('sales') > 0 ? ($monthlyTrends->sum('profit') / $monthlyTrends->sum('sales')) * 100 : 0; @endphp
                                {{ number_format($totalMargin, 1) }}%
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Weekly Trends (Current Month) -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Weekly Performance (Last 4 Weeks)</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Period</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Profit/Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeklyTrends as $week)
                            <tr>
                                <td><strong>{{ $week['week'] }}</strong></td>
                                <td class="text-secondary small">{{ $week['period'] }}</td>
                                <td class="text-end">TZS {{ number_format($week['sales']) }}</td>
                                <td class="text-end text-danger">TZS {{ number_format($week['expenses']) }}</td>
                                <td class="text-end {{ $week['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <strong>TZS {{ number_format($week['profit']) }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">Total (4 Weeks)</th>
                            <th class="text-end text-primary">TZS {{ number_format($weeklyTrends->sum('sales')) }}</th>
                            <th class="text-end text-danger">TZS {{ number_format($weeklyTrends->sum('expenses')) }}</th>
                            <th class="text-end {{ $weeklyTrends->sum('profit') >= 0 ? 'text-success' : 'text-danger' }}">
                                TZS {{ number_format($weeklyTrends->sum('profit')) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
