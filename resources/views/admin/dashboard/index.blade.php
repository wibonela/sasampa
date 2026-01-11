<x-app-layout>
    <div class="fade-in">
        <!-- Header with Quick Actions -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Platform Dashboard</h1>
                <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.companies.index', ['status' => 'pending']) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-building me-1"></i>Review Companies
                </a>
                <a href="{{ route('admin.user-limit-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-people me-1"></i>User Requests
                </a>
            </div>
        </div>

        <!-- Alerts -->
        @if($pendingCompanies->count() > 0)
            <div class="alert alert-warning mb-4 d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>{{ $pendingCompanies->count() }} {{ Str::plural('company', $pendingCompanies->count()) }}</strong> awaiting approval
                </div>
                <a href="{{ route('admin.companies.index', ['status' => 'pending']) }}" class="btn btn-warning btn-sm">Review Now</a>
            </div>
        @endif

        <!-- Primary Stats -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stat-value">{{ $companyStats['total'] }}</div>
                    <div class="stat-label">Total Companies</div>
                    <div class="stat-change {{ $companyStats['approved'] > 0 ? 'text-success' : '' }}">
                        <small><i class="bi bi-check-circle me-1"></i>{{ $companyStats['approved'] }} active</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-value">{{ $totalUsers }}</div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-change text-secondary">
                        <small>{{ $usersByRole['company_owner'] ?? 0 }} owners, {{ $usersByRole['cashier'] ?? 0 }} cashiers</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-value">{{ number_format($transactionStats['total_transactions']) }}</div>
                    <div class="stat-label">Total Transactions</div>
                    <div class="stat-change text-success">
                        <small><i class="bi bi-graph-up me-1"></i>{{ number_format($transactionStats['today_transactions']) }} today</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stat-value">{{ number_format($transactionStats['total_revenue'] / 1000000, 1) }}M</div>
                    <div class="stat-label">Total Revenue (TZS)</div>
                    <div class="stat-change text-success">
                        <small><i class="bi bi-graph-up me-1"></i>{{ number_format($transactionStats['today_revenue']) }} today</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-warning">{{ $companyStats['pending'] }}</div>
                        <div class="text-secondary">Pending Companies</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-danger">{{ $companyStats['rejected'] ?? 0 }}</div>
                        <div class="text-secondary">Rejected Companies</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-info">{{ $userLimitRequests ?? 0 }}</div>
                        <div class="text-secondary">User Limit Requests</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-primary">{{ $feedbackCount ?? 0 }}</div>
                        <div class="text-secondary">New Feedback</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-graph-up me-2"></i>Platform Revenue</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active" data-period="daily">Day</button>
                            <button type="button" class="btn btn-outline-secondary" data-period="weekly">Week</button>
                            <button type="button" class="btn btn-outline-secondary" data-period="monthly">Month</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-pie-chart me-2"></i>Companies by Status
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="companyStatusChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-bar-chart me-2"></i>Company Registrations (Last 14 Days)
                    </div>
                    <div class="card-body">
                        <canvas id="registrationChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-people me-2"></i>Users by Role
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="userRoleChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-trophy me-2"></i>Top Companies by Revenue</span>
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Company</th>
                                    <th class="text-end">Transactions</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCompanies as $index => $company)
                                    <tr>
                                        <td>
                                            @if($index < 3)
                                                <span class="badge {{ $index == 0 ? 'bg-warning' : ($index == 1 ? 'bg-secondary' : 'bg-danger') }}">{{ $index + 1 }}</span>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-decoration-none fw-medium" style="color: var(--apple-text);">
                                                {{ $company['name'] }}
                                            </a>
                                        </td>
                                        <td class="text-end text-secondary">{{ number_format($company['transaction_count']) }}</td>
                                        <td class="text-end fw-medium text-success">{{ number_format($company['total_revenue'], 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">No data yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-clock-history me-2"></i>Recent Activity</span>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                        @forelse($recentActivities as $activity)
                            <div class="d-flex align-items-center px-3 py-3 border-bottom" style="border-color: var(--apple-border) !important;">
                                <div class="me-3" style="width: 36px; height: 36px; border-radius: 10px; background: {{ $activity['type'] === 'transaction' ? 'rgba(52, 199, 89, 0.1)' : ($activity['type'] === 'company_registered' ? 'rgba(0, 122, 255, 0.1)' : 'rgba(255, 149, 0, 0.1)') }}; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi {{ $activity['icon'] }}" style="color: {{ $activity['type'] === 'transaction' ? 'var(--apple-green)' : ($activity['type'] === 'company_registered' ? 'var(--apple-blue)' : 'var(--apple-orange)') }}; font-size: 16px;"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div style="font-size: 13px; font-weight: 500;">{{ $activity['message'] }}</div>
                                    <div class="text-secondary" style="font-size: 11px;">{{ $activity['timestamp']->diffForHumans() }}</div>
                                </div>
                                @if(isset($activity['amount']))
                                    <div style="font-size: 14px; font-weight: 600; color: var(--apple-green);">+{{ number_format($activity['amount'], 0) }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No recent activity
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Companies Quick View -->
        @if($pendingCompanies->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning bg-opacity-10">
                <span><i class="bi bi-hourglass-split me-2"></i>Pending Approval ({{ $pendingCompanies->count() }})</span>
                <a href="{{ route('admin.companies.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning">Review All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Owner</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingCompanies->take(5) as $company)
                            <tr>
                                <td class="fw-medium">{{ $company->name }}</td>
                                <td>{{ $company->owner->name ?? '—' }}</td>
                                <td class="text-secondary">{{ $company->email }}</td>
                                <td class="text-secondary">{{ $company->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <form action="{{ route('admin.companies.approve', $company) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-primary ms-1">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- System Health -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-heart-pulse me-2"></i>System Health
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3" style="width: 12px; height: 12px; border-radius: 50%; background: var(--apple-{{ $systemHealth['database']['status'] === 'healthy' ? 'green' : 'red' }});"></div>
                            <div>
                                <div style="font-size: 14px; font-weight: 600;">Database</div>
                                <div class="text-secondary" style="font-size: 12px;">{{ $systemHealth['database']['message'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3" style="width: 12px; height: 12px; border-radius: 50%; background: var(--apple-{{ $systemHealth['storage']['status'] === 'healthy' ? 'green' : ($systemHealth['storage']['status'] === 'warning' ? 'orange' : 'red') }});"></div>
                            <div>
                                <div style="font-size: 14px; font-weight: 600;">Storage</div>
                                <div class="text-secondary" style="font-size: 12px;">{{ $systemHealth['storage']['message'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3" style="width: 12px; height: 12px; border-radius: 50%; background: var(--apple-{{ $systemHealth['errors']['status'] === 'healthy' ? 'green' : ($systemHealth['errors']['status'] === 'warning' ? 'orange' : 'red') }});"></div>
                            <div>
                                <div style="font-size: 14px; font-weight: 600;">Error Log</div>
                                <div class="text-secondary" style="font-size: 12px;">{{ $systemHealth['errors']['message'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3" style="width: 12px; height: 12px; border-radius: 50%; background: var(--apple-green);"></div>
                            <div>
                                <div style="font-size: 14px; font-weight: 600;">Queue</div>
                                <div class="text-secondary" style="font-size: 12px;">Running normally</div>
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

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: @json($revenueTrends['labels']),
                    datasets: [{
                        label: 'Revenue (TZS)',
                        data: @json($revenueTrends['revenues']),
                        borderColor: '#007AFF',
                        backgroundColor: 'rgba(0, 122, 255, 0.08)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#007AFF',
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1D1D1F',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => 'TZS ' + ctx.raw.toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: { callback: v => (v/1000000).toFixed(1) + 'M' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            // Company Status Pie Chart
            new Chart(document.getElementById('companyStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending', 'Rejected'],
                    datasets: [{
                        data: [{{ $companyStats['approved'] }}, {{ $companyStats['pending'] }}, {{ $companyStats['rejected'] ?? 0 }}],
                        backgroundColor: ['#34C759', '#FF9500', '#FF3B30'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 20, usePointStyle: true }
                        }
                    }
                }
            });

            // Registration Chart
            new Chart(document.getElementById('registrationChart'), {
                type: 'bar',
                data: {
                    labels: @json($registrationTrends['labels']),
                    datasets: [{
                        data: @json($registrationTrends['values']),
                        backgroundColor: '#007AFF',
                        borderRadius: 6,
                        barThickness: 20
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

            // User Role Chart
            new Chart(document.getElementById('userRoleChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Company Owners', 'Cashiers', 'Admins'],
                    datasets: [{
                        data: [{{ $usersByRole['company_owner'] ?? 0 }}, {{ $usersByRole['cashier'] ?? 0 }}, {{ $usersByRole['platform_admin'] ?? 0 }}],
                        backgroundColor: ['#007AFF', '#34C759', '#5856D6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 20, usePointStyle: true }
                        }
                    }
                }
            });

            // Period buttons for revenue chart
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
