<x-app-layout>
    <div class="fade-in">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Platform overview</p>
        </div>

        <!-- Pending Alert -->
        @if($pendingCompanies->count() > 0)
            <div class="alert alert-warning mb-4">
                <strong>{{ $pendingCompanies->count() }} {{ Str::plural('company', $pendingCompanies->count()) }} awaiting approval</strong>
                <a href="{{ route('admin.companies.index', ['status' => 'pending']) }}" class="ms-2" style="color: inherit;">Review &rarr;</a>
            </div>
        @endif

        <!-- Stats -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stat-value">{{ $companyStats['total'] }}</div>
                    <div class="stat-label">Companies</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-value">{{ $totalUsers }}</div>
                    <div class="stat-label">Users</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-value">{{ number_format($transactionStats['total_transactions']) }}</div>
                    <div class="stat-label">Transactions</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div class="stat-value">{{ number_format($transactionStats['total_revenue'], 0) }}</div>
                    <div class="stat-label">Revenue</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Revenue</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active" data-period="daily">Day</button>
                            <button type="button" class="btn btn-outline-secondary" data-period="weekly">Week</button>
                            <button type="button" class="btn btn-outline-secondary" data-period="monthly">Month</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="280"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Registrations</div>
                    <div class="card-body">
                        <canvas id="registrationChart" height="280"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Top Companies</span>
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCompanies as $company)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-decoration-none" style="color: var(--apple-text);">
                                                {{ $company['name'] }}
                                            </a>
                                            <span class="text-secondary" style="font-size: 12px;">{{ $company['transaction_count'] }} transactions</span>
                                        </td>
                                        <td class="text-end" style="font-weight: 500;">{{ number_format($company['total_revenue'], 0) }} TZS</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-secondary">No data yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Recent Activity</span>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse($recentActivities as $activity)
                            <div class="d-flex align-items-center px-3 py-3 border-bottom" style="border-color: var(--apple-border) !important;">
                                <div class="me-3" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                    <i class="bi {{ $activity['icon'] }}" style="color: var(--apple-blue); font-size: 14px;"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div style="font-size: 13px;">{{ $activity['message'] }}</div>
                                    <div class="text-secondary" style="font-size: 11px;">{{ $activity['timestamp']->diffForHumans() }}</div>
                                </div>
                                @if(isset($activity['amount']))
                                    <div style="font-size: 13px; font-weight: 500; color: var(--apple-green);">+{{ number_format($activity['amount'], 0) }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-4 text-secondary">No activity</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="card mt-4">
            <div class="card-header">System Status</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3" style="width: 8px; height: 8px; border-radius: 50%; background: var(--apple-{{ $systemHealth['database']['status'] === 'healthy' ? 'green' : 'red' }});"></div>
                            <div>
                                <div style="font-size: 13px; font-weight: 500;">Database</div>
                                <div class="text-secondary" style="font-size: 12px;">{{ $systemHealth['database']['message'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3" style="width: 8px; height: 8px; border-radius: 50%; background: var(--apple-{{ $systemHealth['storage']['status'] === 'healthy' ? 'green' : ($systemHealth['storage']['status'] === 'warning' ? 'orange' : 'red') }});"></div>
                            <div>
                                <div style="font-size: 13px; font-weight: 500;">Storage</div>
                                <div class="text-secondary" style="font-size: 12px;">{{ $systemHealth['storage']['message'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3" style="width: 8px; height: 8px; border-radius: 50%; background: var(--apple-{{ $systemHealth['errors']['status'] === 'healthy' ? 'green' : ($systemHealth['errors']['status'] === 'warning' ? 'orange' : 'red') }});"></div>
                            <div>
                                <div style="font-size: 13px; font-weight: 500;">Errors</div>
                                <div class="text-secondary" style="font-size: 12px;">{{ $systemHealth['errors']['message'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "-apple-system, BlinkMacSystemFont, 'SF Pro Text', sans-serif";
            Chart.defaults.color = '#86868B';

            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: @json($revenueTrends['labels']),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($revenueTrends['revenues']),
                        borderColor: '#007AFF',
                        backgroundColor: 'rgba(0, 122, 255, 0.05)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: { callback: v => v.toLocaleString() }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            new Chart(document.getElementById('registrationChart'), {
                type: 'bar',
                data: {
                    labels: @json($registrationTrends['labels']),
                    datasets: [{
                        data: @json($registrationTrends['values']),
                        backgroundColor: '#007AFF',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });

            document.querySelectorAll('[data-period]').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    fetch(`{{ route('admin.dashboard.chart-data') }}?period=${this.dataset.period}&type=revenue`)
                        .then(r => r.json())
                        .then(data => {
                            revenueChart.data.labels = data.labels;
                            revenueChart.data.datasets[0].data = data.revenues;
                            revenueChart.update();
                        });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
