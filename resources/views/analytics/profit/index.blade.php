<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Profit Analytics</h1>
                <p class="page-subtitle">Revenue, Cost of Goods, Expenses & Profit Analysis</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('analytics.profit.branches') }}" class="btn btn-outline-primary">
                    <i class="bi bi-building me-1"></i>By Branch
                </a>
                <a href="{{ route('analytics.profit.trends') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-graph-up me-1"></i>Trends
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('analytics.profit') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select" onchange="toggleCustomDates(this.value)">
                            <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ request('date_from') ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                    <div class="col-md-2 custom-date-field" style="{{ request('date_from') ? '' : 'display:none;' }}">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom) }}">
                    </div>
                    <div class="col-md-2 custom-date-field" style="{{ request('date_from') ? '' : 'display:none;' }}">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo) }}">
                    </div>
                    @if($branches->count() > 0)
                    <div class="col-md-3">
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
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profit Breakdown Cards -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Profit Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <!-- Revenue -->
                    <div class="col-6 col-md-2">
                        <div class="p-3 rounded" style="background: rgba(0, 122, 255, 0.1);">
                            <div class="small text-secondary">Revenue</div>
                            <h5 class="mb-0 text-primary">{{ number_format($totalRevenue / 1000) }}K</h5>
                            @if($revenueGrowth != 0)
                                <small class="{{ $revenueGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bi {{ $revenueGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                                    {{ number_format(abs($revenueGrowth), 1) }}%
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="col-auto d-none d-md-flex align-items-center">
                        <i class="bi bi-dash-lg text-secondary"></i>
                    </div>
                    <!-- COGS -->
                    <div class="col-6 col-md-2">
                        <div class="p-3 rounded" style="background: rgba(255, 149, 0, 0.1);">
                            <div class="small text-secondary">Cost of Goods</div>
                            <h5 class="mb-0 text-warning">{{ number_format($cogs / 1000) }}K</h5>
                            <small class="text-secondary">Product costs</small>
                        </div>
                    </div>
                    <div class="col-auto d-none d-md-flex align-items-center">
                        <i class="bi bi-arrow-right text-secondary"></i>
                    </div>
                    <!-- Gross Profit -->
                    <div class="col-6 col-md-2">
                        <div class="p-3 rounded" style="background: {{ $grossProfit >= 0 ? 'rgba(52, 199, 89, 0.1)' : 'rgba(255, 59, 48, 0.1)' }};">
                            <div class="small text-secondary">Gross Profit</div>
                            <h5 class="mb-0 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($grossProfit / 1000) }}K</h5>
                            <small class="{{ $grossMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($grossMargin, 1) }}% margin</small>
                        </div>
                    </div>
                    <div class="col-auto d-none d-md-flex align-items-center">
                        <i class="bi bi-dash-lg text-secondary"></i>
                    </div>
                    <!-- Operating Expenses -->
                    <div class="col-6 col-md-2">
                        <div class="p-3 rounded" style="background: rgba(255, 59, 48, 0.1);">
                            <div class="small text-secondary">Expenses</div>
                            <h5 class="mb-0 text-danger">{{ number_format($operatingExpenses / 1000) }}K</h5>
                            <small class="text-secondary">Matumizi</small>
                        </div>
                    </div>
                    <div class="col-auto d-none d-md-flex align-items-center">
                        <i class="bi bi-arrow-right text-secondary"></i>
                    </div>
                    <!-- Net Profit -->
                    <div class="col-12 col-md-2">
                        <div class="p-3 rounded" style="background: {{ $netProfit >= 0 ? 'rgba(52, 199, 89, 0.2)' : 'rgba(255, 59, 48, 0.2)' }}; border: 2px solid {{ $netProfit >= 0 ? 'var(--apple-green)' : 'var(--apple-red)' }};">
                            <div class="small text-secondary">Net Profit</div>
                            <h4 class="mb-0 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netProfit / 1000) }}K</h4>
                            <small class="{{ $netMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netMargin, 1) }}% margin</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row g-3 mb-4">
            <!-- Net Profit - Main Card -->
            <div class="col-12 col-md-4">
                <div class="card h-100 {{ $netProfit >= 0 ? 'border-success' : 'border-danger' }}" style="border-width: 2px;">
                    <div class="card-body text-center py-4">
                        <div style="width: 64px; height: 64px; border-radius: 16px; background: {{ $netProfit >= 0 ? 'rgba(52, 199, 89, 0.15)' : 'rgba(255, 59, 48, 0.15)' }}; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <i class="bi {{ $netProfit >= 0 ? 'bi-trophy' : 'bi-exclamation-triangle' }}" style="font-size: 28px; color: {{ $netProfit >= 0 ? 'var(--apple-green)' : 'var(--apple-red)' }};"></i>
                        </div>
                        <h2 class="mb-1 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            TZS {{ number_format(abs($netProfit)) }}
                        </h2>
                        <p class="text-secondary mb-2">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</p>
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            @if($netProfitGrowth != 0)
                                <span class="badge {{ $netProfitGrowth >= 0 ? 'bg-success' : 'bg-danger' }}">
                                    <i class="bi {{ $netProfitGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                                    {{ number_format(abs($netProfitGrowth), 1) }}%
                                </span>
                                <small class="text-secondary">vs previous period</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="col-6 col-md-4">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-graph-up-arrow" style="font-size: 20px; color: var(--apple-blue);"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-secondary small">Total Revenue</div>
                                <h4 class="mb-0">TZS {{ number_format($totalRevenue) }}</h4>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between small">
                            <span class="text-secondary">{{ $transactionCount }} transactions</span>
                            @if($revenueGrowth != 0)
                                <span class="{{ $revenueGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bi {{ $revenueGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                                    {{ number_format(abs($revenueGrowth), 1) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gross Profit -->
            <div class="col-6 col-md-4">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(52, 199, 89, 0.1); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-cash-stack" style="font-size: 20px; color: var(--apple-green);"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-secondary small">Gross Profit</div>
                                <h4 class="mb-0 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">TZS {{ number_format($grossProfit) }}</h4>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between small">
                            <span class="text-secondary">{{ number_format($grossMargin, 1) }}% margin</span>
                            @if($grossProfitGrowth != 0)
                                <span class="{{ $grossProfitGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bi {{ $grossProfitGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                                    {{ number_format(abs($grossProfitGrowth), 1) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small mb-1">Cost of Goods Sold</div>
                        <h4 class="mb-0 text-warning">TZS {{ number_format($cogs) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small mb-1">Operating Expenses</div>
                        <h4 class="mb-0 text-danger">TZS {{ number_format($operatingExpenses) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small mb-1">Avg Transaction</div>
                        <h4 class="mb-0">TZS {{ number_format($avgTransactionValue) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small mb-1">Profitable Days</div>
                        <h4 class="mb-0 text-success">{{ $performanceData['profitable_days'] ?? 0 }} / {{ $performanceData['total_days'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Trend Chart Data -->
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daily Breakdown</h5>
                        <small class="text-secondary">{{ \Carbon\Carbon::parse($dateFrom)->format('d M') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</small>
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Sales</th>
                                    <th class="text-end">Expenses</th>
                                    <th class="text-end">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trendData as $data)
                                    <tr>
                                        <td>{{ $period == 'today' ? $data['period'] : \Carbon\Carbon::parse($data['period'])->format('D, d M') }}</td>
                                        <td class="text-end text-primary">{{ number_format($data['sales']) }}</td>
                                        <td class="text-end text-danger">{{ number_format($data['expenses']) }}</td>
                                        <td class="text-end {{ $data['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ number_format($data['profit']) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Top Expense Categories -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Top Expense Categories</h5>
                    </div>
                    <div class="card-body">
                        @forelse($topExpenseCategories as $category)
                            @php $pct = $operatingExpenses > 0 ? ($category->total / $operatingExpenses) * 100 : 0; @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">{{ $category->category }}</span>
                                    <span class="small text-secondary">{{ number_format($pct, 0) }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="text-end small text-secondary mt-1">TZS {{ number_format($category->total) }}</div>
                            </div>
                        @empty
                            <p class="text-secondary text-center mb-0">No expenses</p>
                        @endforelse
                    </div>
                </div>

                <!-- Best/Worst Day -->
                @if(isset($performanceData['best_day']))
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Performance Highlights</h5>
                    </div>
                    <div class="card-body">
                        @if($performanceData['best_day'])
                        <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded" style="background: rgba(52, 199, 89, 0.1);">
                            <i class="bi bi-arrow-up-circle-fill text-success" style="font-size: 24px;"></i>
                            <div>
                                <div class="small text-secondary">Best Day</div>
                                <div class="fw-bold text-success">TZS {{ number_format($performanceData['best_day']['profit']) }}</div>
                                <div class="small text-secondary">{{ \Carbon\Carbon::parse($performanceData['best_day']['date'])->format('D, d M') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($performanceData['worst_day'])
                        <div class="d-flex align-items-center gap-3 p-2 rounded" style="background: rgba(255, 59, 48, 0.1);">
                            <i class="bi bi-arrow-down-circle-fill text-danger" style="font-size: 24px;"></i>
                            <div>
                                <div class="small text-secondary">Worst Day</div>
                                <div class="fw-bold text-danger">TZS {{ number_format($performanceData['worst_day']['profit']) }}</div>
                                <div class="small text-secondary">{{ \Carbon\Carbon::parse($performanceData['worst_day']['date'])->format('D, d M') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Branch Comparison (if multiple branches) -->
        @if($branchComparison && $branchComparison->count() > 0)
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Branch Comparison</h5>
                <a href="{{ route('analytics.profit.branches') }}" class="btn btn-sm btn-outline-primary">View Details</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branchComparison as $branch)
                            <tr>
                                <td>{{ $branch['name'] }}</td>
                                <td class="text-end">TZS {{ number_format($branch['sales']) }}</td>
                                <td class="text-end text-danger">TZS {{ number_format($branch['expenses']) }}</td>
                                <td class="text-end {{ $branch['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <strong>TZS {{ number_format($branch['profit']) }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Payment Breakdown -->
        @if($paymentBreakdown->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Sales by Payment Method</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($paymentBreakdown as $payment)
                        @php
                            $label = match($payment->payment_method) {
                                'cash' => ['Cash', 'bi-cash', 'success'],
                                'mobile' => ['Mobile Money', 'bi-phone', 'info'],
                                'card' => ['Card', 'bi-credit-card', 'primary'],
                                'bank_transfer' => ['Bank Transfer', 'bi-bank', 'secondary'],
                                default => [ucfirst($payment->payment_method), 'bi-wallet', 'secondary']
                            };
                        @endphp
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center gap-2 p-3 rounded" style="background: var(--apple-gray-6);">
                                <i class="bi {{ $label[1] }} text-{{ $label[2] }}" style="font-size: 24px;"></i>
                                <div>
                                    <div class="small text-secondary">{{ $label[0] }}</div>
                                    <div class="fw-bold">TZS {{ number_format($payment->total) }}</div>
                                    <div class="small text-secondary">{{ $payment->count }} txns</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function toggleCustomDates(value) {
            const fields = document.querySelectorAll('.custom-date-field');
            fields.forEach(f => f.style.display = value === 'custom' ? 'block' : 'none');
        }
    </script>
    @endpush
</x-app-layout>
