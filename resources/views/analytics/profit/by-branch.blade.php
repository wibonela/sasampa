<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Branch Profit Analysis</h1>
                <p class="page-subtitle">Compare performance across branches</p>
            </div>
            <a href="{{ route('analytics.profit') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Analytics
            </a>
        </div>

        <!-- Period Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('analytics.profit.branches') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select">
                            <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Company Totals -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small">Total Sales</div>
                        <h4 class="mb-0 text-primary">TZS {{ number_format($totalSales) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small">Total Expenses</div>
                        <h4 class="mb-0 text-danger">TZS {{ number_format($totalExpenses) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card {{ $totalProfit >= 0 ? 'border-success' : 'border-danger' }}">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small">Net Profit</div>
                        <h4 class="mb-0 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">TZS {{ number_format($totalProfit) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-3 text-center">
                        <div class="text-secondary small">Total Transactions</div>
                        <h4 class="mb-0">{{ number_format($totalTransactions) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best & Worst Performers -->
        @if($branchData->count() > 1)
        <div class="row g-3 mb-4">
            @if($bestBranch)
            <div class="col-md-6">
                <div class="card h-100" style="border-left: 4px solid var(--apple-green);">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(52, 199, 89, 0.15); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-trophy-fill text-success" style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <div class="small text-secondary">Best Performing Branch</div>
                                <h4 class="mb-0">{{ $bestBranch['branch']->name }}</h4>
                                <div class="text-success">Profit: TZS {{ number_format($bestBranch['profit']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if($worstBranch && $worstBranch['branch']->id !== $bestBranch['branch']->id)
            <div class="col-md-6">
                <div class="card h-100" style="border-left: 4px solid var(--apple-red);">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(255, 59, 48, 0.15); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <div class="small text-secondary">Needs Attention</div>
                                <h4 class="mb-0">{{ $worstBranch['branch']->name }}</h4>
                                <div class="{{ $worstBranch['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $worstBranch['profit'] >= 0 ? 'Profit' : 'Loss' }}: TZS {{ number_format(abs($worstBranch['profit'])) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Branch Cards -->
        <div class="row g-4">
            @forelse($branchData as $data)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-building me-2 text-secondary"></i>
                                {{ $data['branch']->name }}
                            </h5>
                            @if($data['branch']->is_main)
                                <span class="badge bg-primary">Main</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <!-- Profit/Loss Highlight -->
                            <div class="text-center py-3 mb-3 rounded" style="background: {{ $data['profit'] >= 0 ? 'rgba(52, 199, 89, 0.1)' : 'rgba(255, 59, 48, 0.1)' }};">
                                <div class="small text-secondary">{{ $data['profit'] >= 0 ? 'Profit' : 'Loss' }}</div>
                                <h3 class="mb-0 {{ $data['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    TZS {{ number_format(abs($data['profit'])) }}
                                </h3>
                                <div class="small {{ $data['margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($data['margin'], 1) }}% margin
                                </div>
                            </div>

                            <!-- Metrics -->
                            <div class="row g-2 text-center">
                                <div class="col-6">
                                    <div class="p-2 rounded" style="background: var(--apple-gray-6);">
                                        <div class="small text-secondary">Sales</div>
                                        <div class="fw-bold text-primary">{{ number_format($data['sales'] / 1000) }}K</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded" style="background: var(--apple-gray-6);">
                                        <div class="small text-secondary">Expenses</div>
                                        <div class="fw-bold text-danger">{{ number_format($data['expenses'] / 1000) }}K</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded" style="background: var(--apple-gray-6);">
                                        <div class="small text-secondary">Transactions</div>
                                        <div class="fw-bold">{{ number_format($data['transactions']) }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded" style="background: var(--apple-gray-6);">
                                        <div class="small text-secondary">Avg Sale</div>
                                        <div class="fw-bold">{{ number_format($data['avg_transaction']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('analytics.profit', ['branch' => $data['branch']->id, 'period' => $period]) }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5 text-secondary">
                            <i class="bi bi-building" style="font-size: 48px;"></i>
                            <p class="mt-3 mb-0">No branches found</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Comparison Table -->
        @if($branchData->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Detailed Comparison</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Expenses</th>
                            <th class="text-end">Profit/Loss</th>
                            <th class="text-end">Margin</th>
                            <th class="text-end">Transactions</th>
                            <th class="text-end">Avg Sale</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branchData as $data)
                            <tr>
                                <td>
                                    <strong>{{ $data['branch']->name }}</strong>
                                    @if($data['branch']->is_main)
                                        <span class="badge bg-primary ms-1">Main</span>
                                    @endif
                                </td>
                                <td class="text-end text-primary">TZS {{ number_format($data['sales']) }}</td>
                                <td class="text-end text-danger">TZS {{ number_format($data['expenses']) }}</td>
                                <td class="text-end {{ $data['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <strong>TZS {{ number_format($data['profit']) }}</strong>
                                </td>
                                <td class="text-end {{ $data['margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($data['margin'], 1) }}%
                                </td>
                                <td class="text-end">{{ number_format($data['transactions']) }}</td>
                                <td class="text-end">TZS {{ number_format($data['avg_transaction']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-end text-primary">TZS {{ number_format($totalSales) }}</th>
                            <th class="text-end text-danger">TZS {{ number_format($totalExpenses) }}</th>
                            <th class="text-end {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">TZS {{ number_format($totalProfit) }}</th>
                            <th class="text-end">{{ $totalSales > 0 ? number_format(($totalProfit / $totalSales) * 100, 1) : 0 }}%</th>
                            <th class="text-end">{{ number_format($totalTransactions) }}</th>
                            <th class="text-end">TZS {{ $totalTransactions > 0 ? number_format($totalSales / $totalTransactions) : 0 }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
