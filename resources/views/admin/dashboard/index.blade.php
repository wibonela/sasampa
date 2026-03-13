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

        <!-- ═══════════════ PRIMARY STATS ═══════════════ -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-building"></i></div>
                    <div class="stat-value">{{ $companyStats['total'] }}</div>
                    <div class="stat-label">Total Companies</div>
                    <div class="stat-change text-success"><small><i class="bi bi-check-circle me-1"></i>{{ $companyStats['approved'] }} active</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-value">{{ $totalUsers }}</div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-change text-secondary"><small>{{ $usersByRole['company_owner'] ?? 0 }} owners, {{ $usersByRole['cashier'] ?? 0 }} cashiers</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-receipt"></i></div>
                    <div class="stat-value">{{ number_format($transactionStats['total_transactions']) }}</div>
                    <div class="stat-label">Total Transactions</div>
                    <div class="stat-change text-success"><small><i class="bi bi-graph-up me-1"></i>{{ number_format($transactionStats['today_transactions']) }} today</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
                    <div class="stat-value">{{ number_format($transactionStats['total_revenue'] / 1000000, 1) }}M</div>
                    <div class="stat-label">Total Revenue (TZS)</div>
                    <div class="stat-change text-success"><small><i class="bi bi-graph-up me-1"></i>{{ number_format($transactionStats['today_revenue']) }} today</small></div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-warning">{{ $companyStats['pending'] }}</div>
                        <div class="text-secondary" style="font-size: 13px;">Pending Companies</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold" style="color: #007AFF;">{{ $activeCompanyStats['active_today'] }}</div>
                        <div class="text-secondary" style="font-size: 13px;">Active Today</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold" style="color: #34C759;">{{ $customerStats['total'] }}</div>
                        <div class="text-secondary" style="font-size: 13px;">Total Customers</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold" style="color: #5856D6;">{{ $mobileAppStats['active_devices'] }}</div>
                        <div class="text-secondary" style="font-size: 13px;">Active Devices</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════ CHARTS ROW ═══════════════ -->
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
                    <div class="card-header"><i class="bi bi-pie-chart me-2"></i>Companies by Status</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="companyStatusChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════ 1. CUSTOMER GROWTH & ENGAGEMENT ═══════════════ -->
        <div class="mb-2 mt-5">
            <h5 class="fw-semibold" style="color: var(--apple-text);"><i class="bi bi-person-lines-fill me-2" style="color: #007AFF;"></i>Customer Intelligence</h5>
        </div>

        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-person-lines-fill"></i></div>
                    <div class="stat-value">{{ number_format($customerStats['total']) }}</div>
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-change text-success"><small><i class="bi bi-plus-circle me-1"></i>{{ $customerStats['new_this_week'] }} this week</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-person-plus"></i></div>
                    <div class="stat-value">{{ $customerStats['new_this_month'] }}</div>
                    <div class="stat-label">New This Month</div>
                    <div class="stat-change text-secondary"><small>Across all companies</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-person-check"></i></div>
                    <div class="stat-value">{{ $customerBreakdown['registered_pct'] }}%</div>
                    <div class="stat-label">Registered Sales</div>
                    <div class="stat-change text-secondary"><small>{{ $customerBreakdown['walkin_pct'] }}% walk-in</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="bi bi-graph-up-arrow"></i></div>
                    <div class="stat-value">{{ $activeCompanyStats['avg_txn_per_company_per_day'] }}</div>
                    <div class="stat-label">Avg Txn/Company/Day</div>
                    <div class="stat-change text-secondary"><small>Last 30 days</small></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-graph-up me-2"></i>Customer Growth (Last 30 Days)</div>
                    <div class="card-body"><canvas id="customerGrowthChart" height="220"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-people me-2"></i>Registered vs Walk-in</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="customerBreakdownChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        @if($customerStats['top_companies']->count() > 0)
        <div class="row g-3 mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header"><i class="bi bi-trophy me-2"></i>Companies by Customer Count</div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>#</th><th>Company</th><th class="text-end">Customers</th></tr></thead>
                            <tbody>
                                @foreach($customerStats['top_companies'] as $i => $c)
                                <tr>
                                    <td>@if($i < 3)<span class="badge {{ $i == 0 ? 'bg-warning' : ($i == 1 ? 'bg-secondary' : 'bg-danger') }}">{{ $i + 1 }}</span>@else {{ $i + 1 }} @endif</td>
                                    <td class="fw-medium">{{ $c->name }}</td>
                                    <td class="text-end fw-semibold" style="color: #007AFF;">{{ number_format($c->customer_count) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- ═══════════════ 2. PRODUCT INTELLIGENCE ═══════════════ -->
        <div class="mb-2 mt-5">
            <h5 class="fw-semibold" style="color: var(--apple-text);"><i class="bi bi-box-seam me-2" style="color: #FF9500;"></i>Product Intelligence</h5>
        </div>

        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-box-seam"></i></div>
                    <div class="stat-value">{{ number_format($productCatalogStats['total_products']) }}</div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-change text-secondary"><small>~{{ $productCatalogStats['avg_per_company'] }} per company</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-shop"></i></div>
                    <div class="stat-value">{{ $productCatalogStats['companies_with_products'] }}</div>
                    <div class="stat-label">Companies with Products</div>
                    <div class="stat-change text-secondary"><small>Have catalog setup</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-value">{{ count($lowStockAlerts) }}</div>
                    <div class="stat-label">Low Stock Companies</div>
                    <div class="stat-change text-danger"><small>Need restocking</small></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @if(count($trendingProducts) > 0)
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-fire me-2"></i>Trending Products (Last 30 Days)</div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>#</th><th>Product</th><th class="text-end">Sold</th><th class="text-end">Revenue</th><th class="text-end">Companies</th></tr></thead>
                            <tbody>
                                @foreach($trendingProducts as $i => $p)
                                <tr>
                                    <td>@if($i < 3)<span class="badge {{ $i == 0 ? 'bg-warning' : ($i == 1 ? 'bg-secondary' : 'bg-danger') }}">{{ $i + 1 }}</span>@else {{ $i + 1 }} @endif</td>
                                    <td class="fw-medium">{{ $p->product_name }}</td>
                                    <td class="text-end">{{ number_format($p->total_sold) }}</td>
                                    <td class="text-end text-success fw-medium">{{ number_format($p->total_revenue, 0) }}</td>
                                    <td class="text-end"><span class="badge bg-primary bg-opacity-10 text-primary">{{ $p->companies_selling }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if(count($lowStockAlerts) > 0)
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock Alerts</div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Company</th><th class="text-end">Low Stock Items</th></tr></thead>
                            <tbody>
                                @foreach($lowStockAlerts as $alert)
                                <tr>
                                    <td class="fw-medium">{{ $alert->name }}</td>
                                    <td class="text-end"><span class="badge bg-danger">{{ $alert->low_stock_count }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- ═══════════════ 3. USAGE & ADOPTION ═══════════════ -->
        <div class="mb-2 mt-5">
            <h5 class="fw-semibold" style="color: var(--apple-text);"><i class="bi bi-bar-chart-line me-2" style="color: #34C759;"></i>Usage & Adoption</h5>
        </div>

        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-shop-window"></i></div>
                    <div class="stat-value">{{ $activeCompanyStats['active_today'] }}</div>
                    <div class="stat-label">Active Today</div>
                    <div class="stat-change text-secondary"><small>of {{ $activeCompanyStats['approved_total'] }} approved</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-calendar-week"></i></div>
                    <div class="stat-value">{{ $activeCompanyStats['active_this_week'] }}</div>
                    <div class="stat-label">Active This Week</div>
                    <div class="stat-change text-secondary"><small>{{ $activeCompanyStats['approved_total'] > 0 ? round(($activeCompanyStats['active_this_week'] / $activeCompanyStats['approved_total']) * 100) : 0 }}% of approved</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon red"><i class="bi bi-exclamation-diamond"></i></div>
                    <div class="stat-value">{{ $activeCompanyStats['churn_risk']->count() }}</div>
                    <div class="stat-label">Churn Risk</div>
                    <div class="stat-change text-danger"><small>Inactive 7+ days</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-chat-square-text"></i></div>
                    <div class="stat-value">{{ $feedbackCount ?? 0 }}</div>
                    <div class="stat-label">New Feedback</div>
                    <div class="stat-change text-secondary"><small>{{ $sandukuSummary['total'] }} total</small></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-activity me-2"></i>Daily Active Companies (Last 14 Days)</div>
                    <div class="card-body"><canvas id="activeCompaniesChart" height="220"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-puzzle me-2"></i>Feature Adoption</div>
                    <div class="card-body">
                        @php
                            $featureLabels = [
                                'mobile_app' => ['Mobile App', '#007AFF'],
                                'customers' => ['Customers', '#34C759'],
                                'expenses' => ['Expenses', '#FF9500'],
                                'orders' => ['Orders', '#5856D6'],
                                'whatsapp' => ['WhatsApp', '#25D366'],
                                'branches' => ['Branches', '#FF3B30'],
                            ];
                        @endphp
                        @foreach($featureLabels as $key => [$label, $color])
                            @php $feat = $featureAdoption['features'][$key] ?? ['count' => 0, 'pct' => 0]; @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="font-size: 13px; font-weight: 500;">{{ $label }}</span>
                                    <span style="font-size: 12px; color: #86868B;">{{ $feat['count'] }}/{{ $featureAdoption['total_companies'] }} ({{ $feat['pct'] }}%)</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ $feat['pct'] }}%; background-color: {{ $color }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if($activeCompanyStats['churn_risk']->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-danger bg-opacity-10"><i class="bi bi-exclamation-diamond text-danger me-2"></i>Churn Risk — Inactive 7+ Days</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Company</th><th>Registered</th><th class="text-end">Days Inactive</th></tr></thead>
                    <tbody>
                        @foreach($activeCompanyStats['churn_risk'] as $company)
                        <tr>
                            <td class="fw-medium">
                                <a href="{{ route('admin.companies.show', $company->id) }}" class="text-decoration-none" style="color: var(--apple-text);">{{ $company->name }}</a>
                            </td>
                            <td class="text-secondary">{{ $company->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                @php
                                    $lastTx = \App\Models\Transaction::withoutGlobalScope('company')->where('company_id', $company->id)->latest()->first();
                                    $daysInactive = $lastTx ? $lastTx->created_at->diffInDays(now()) : $company->created_at->diffInDays(now());
                                @endphp
                                <span class="badge {{ $daysInactive > 14 ? 'bg-danger' : 'bg-warning' }}">{{ $daysInactive }} days</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- ═══════════════ 4. BUSINESS HEALTH ═══════════════ -->
        <div class="mb-2 mt-5">
            <h5 class="fw-semibold" style="color: var(--apple-text);"><i class="bi bi-heart-pulse me-2" style="color: #FF3B30;"></i>Business Health</h5>
        </div>

        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-cash-coin"></i></div>
                    <div class="stat-value">{{ number_format($revenuePerCompany['avg_revenue']) }}</div>
                    <div class="stat-label">Avg Revenue/Company (TZS)</div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-pie-chart"></i></div>
                    <div class="stat-value">{{ $revenuePerCompany['top3_concentration_pct'] }}%</div>
                    <div class="stat-label">Top 3 Concentration</div>
                    <div class="stat-change text-secondary"><small>Revenue share of top 3 companies</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon red"><i class="bi bi-graph-down-arrow"></i></div>
                    <div class="stat-value">{{ count($decliningCompanies) }}</div>
                    <div class="stat-label">Declining Companies</div>
                    <div class="stat-change text-danger"><small>Revenue dropped vs prior month</small></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Top Companies Table -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-trophy me-2"></i>Top Companies by Revenue</span>
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>#</th><th>Company</th><th class="text-end">Transactions</th><th class="text-end">Revenue</th></tr></thead>
                            <tbody>
                                @forelse($topCompanies as $index => $company)
                                <tr>
                                    <td>@if($index < 3)<span class="badge {{ $index == 0 ? 'bg-warning' : ($index == 1 ? 'bg-secondary' : 'bg-danger') }}">{{ $index + 1 }}</span>@else {{ $index + 1 }} @endif</td>
                                    <td><a href="{{ route('admin.companies.show', $company['id']) }}" class="text-decoration-none fw-medium" style="color: var(--apple-text);">{{ $company['name'] }}</a></td>
                                    <td class="text-end text-secondary">{{ number_format($company['transaction_count']) }}</td>
                                    <td class="text-end fw-medium text-success">{{ number_format($company['total_revenue'], 0) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-secondary">No data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Declining Companies -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-graph-down-arrow text-danger me-2"></i>Declining Revenue</div>
                    @if(count($decliningCompanies) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Company</th><th class="text-end">Last 30d</th><th class="text-end">Prior 30d</th><th class="text-end">Change</th></tr></thead>
                            <tbody>
                                @foreach($decliningCompanies as $dc)
                                @php $declinePct = $dc->previous_revenue > 0 ? round((($dc->recent_revenue - $dc->previous_revenue) / $dc->previous_revenue) * 100, 1) : 0; @endphp
                                <tr>
                                    <td class="fw-medium">{{ $dc->name }}</td>
                                    <td class="text-end">{{ number_format($dc->recent_revenue, 0) }}</td>
                                    <td class="text-end text-secondary">{{ number_format($dc->previous_revenue, 0) }}</td>
                                    <td class="text-end"><span class="badge bg-danger">{{ $declinePct }}%</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="card-body text-center py-5 text-secondary">
                        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                        No companies with declining revenue
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ═══════════════ 5. MOBILE APP ANALYTICS ═══════════════ -->
        <div class="mb-2 mt-5">
            <h5 class="fw-semibold" style="color: var(--apple-text);"><i class="bi bi-phone me-2" style="color: #5856D6;"></i>Mobile App Analytics</h5>
        </div>

        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="bi bi-phone"></i></div>
                    <div class="stat-value">{{ $mobileAppStats['total_devices'] }}</div>
                    <div class="stat-label">Total Devices</div>
                    <div class="stat-change text-success"><small>{{ $mobileAppStats['active_devices'] }} active</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-person-badge"></i></div>
                    <div class="stat-value">{{ $mobileAppStats['mobile_users'] }}</div>
                    <div class="stat-label">Mobile Users</div>
                    <div class="stat-change text-secondary"><small>{{ $mobileAppStats['web_only_users'] }} web-only</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-value">{{ $mobileAppStats['pending_requests']->count() }}</div>
                    <div class="stat-label">Pending Requests</div>
                    <div class="stat-change text-warning"><small>Awaiting approval</small></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-download"></i></div>
                    <div class="stat-value">{{ $mobileAppStats['version_distribution']->count() }}</div>
                    <div class="stat-label">App Versions</div>
                    <div class="stat-change text-secondary"><small>In the wild</small></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @if($mobileAppStats['version_distribution']->count() > 0)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-cpu me-2"></i>App Version Distribution</div>
                    <div class="card-body">
                        @php $maxVersionCount = $mobileAppStats['version_distribution']->max('count') ?: 1; @endphp
                        @foreach($mobileAppStats['version_distribution'] as $v)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span style="font-size: 13px; font-weight: 500;">v{{ $v->app_version ?? 'Unknown' }}</span>
                                <span style="font-size: 12px; color: #86868B;">{{ $v->count }} devices</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ ($v->count / $maxVersionCount) * 100 }}%; background-color: #5856D6;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if($mobileAppStats['pending_requests']->count() > 0)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-warning bg-opacity-10"><i class="bi bi-hourglass-split text-warning me-2"></i>Pending Mobile Access</div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Company</th><th>Requested</th><th class="text-end">Waiting</th></tr></thead>
                            <tbody>
                                @foreach($mobileAppStats['pending_requests'] as $req)
                                <tr>
                                    <td class="fw-medium">{{ $req['company_name'] }}</td>
                                    <td class="text-secondary">{{ $req['requested_at'] }}</td>
                                    <td class="text-end"><span class="badge {{ $req['days_waiting'] > 3 ? 'bg-danger' : 'bg-warning' }}">{{ $req['days_waiting'] }} days</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- ═══════════════ 6. RETENTION & ONBOARDING ═══════════════ -->
        <div class="mb-2 mt-5">
            <h5 class="fw-semibold" style="color: var(--apple-text);"><i class="bi bi-funnel me-2" style="color: #FF9500;"></i>Retention & Onboarding</h5>
        </div>

        <div class="row g-3 mb-4">
            <!-- Company Age -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-calendar3 me-2"></i>Company Age</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="companyAgingChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Onboarding Funnel -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-funnel me-2"></i>Onboarding Funnel</div>
                    <div class="card-body">
                        @php
                            $funnelTotal = $onboardingFunnel['completed'] + $onboardingFunnel['step_1'] + $onboardingFunnel['step_2'] + $onboardingFunnel['step_3'] + $onboardingFunnel['step_4'];
                            $funnelMax = max(1, $funnelTotal);
                        @endphp
                        @foreach([
                            ['Completed', $onboardingFunnel['completed'], '#34C759'],
                            ['Step 4 — Final Setup', $onboardingFunnel['step_4'], '#007AFF'],
                            ['Step 3 — Business Info', $onboardingFunnel['step_3'], '#5856D6'],
                            ['Step 2 — Verification', $onboardingFunnel['step_2'], '#FF9500'],
                            ['Step 1 — Registered', $onboardingFunnel['step_1'], '#FF3B30'],
                        ] as [$label, $count, $color])
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span style="font-size: 12px; font-weight: 500;">{{ $label }}</span>
                                <span style="font-size: 12px; font-weight: 600;">{{ $count }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ ($count / $funnelMax) * 100 }}%; background-color: {{ $color }};"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Feedback Summary -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-chat-dots me-2"></i>Feedback Summary</span>
                        <a href="{{ route('admin.sanduku.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="fw-bold" style="font-size: 24px; color: #007AFF;">{{ $sandukuSummary['by_status']['new'] ?? 0 }}</div>
                                <div class="text-secondary" style="font-size: 11px;">New</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold" style="font-size: 24px; color: #FF9500;">{{ $sandukuSummary['by_status']['reviewed'] ?? 0 }}</div>
                                <div class="text-secondary" style="font-size: 11px;">Reviewed</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold" style="font-size: 24px; color: #34C759;">{{ $sandukuSummary['by_status']['resolved'] ?? 0 }}</div>
                                <div class="text-secondary" style="font-size: 11px;">Resolved</div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-around text-center">
                            <div>
                                <div class="fw-bold" style="font-size: 18px;">{{ $sandukuSummary['by_type']['feedback'] ?? 0 }}</div>
                                <div class="text-secondary" style="font-size: 11px;"><i class="bi bi-chat-square-text me-1"></i>Feedback</div>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 18px;">{{ $sandukuSummary['by_type']['bug'] ?? 0 }}</div>
                                <div class="text-secondary" style="font-size: 11px;"><i class="bi bi-bug me-1"></i>Bugs</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════ RECENT ACTIVITY + PENDING ═══════════════ -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-clock-history me-2"></i>Recent Activity</span>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                        @forelse($recentActivities as $activity)
                            <div class="d-flex align-items-center px-3 py-3 border-bottom" style="border-color: var(--apple-border) !important;">
                                <div class="me-3" style="width: 36px; height: 36px; border-radius: 10px; background: #fff; border: 1px solid var(--apple-border); display: flex; align-items: center; justify-content: center;">
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
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>No recent activity
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Registration + Users Charts -->
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><i class="bi bi-bar-chart me-2"></i>Company Registrations (Last 14 Days)</div>
                            <div class="card-body"><canvas id="registrationChart" height="140"></canvas></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><i class="bi bi-people me-2"></i>Users by Role</div>
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <canvas id="userRoleChart" height="140"></canvas>
                            </div>
                        </div>
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
                    <thead><tr><th>Company</th><th>Owner</th><th>Email</th><th>Registered</th><th></th></tr></thead>
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
                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-primary ms-1"><i class="bi bi-eye"></i></a>
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
            <div class="card-header"><i class="bi bi-heart-pulse me-2"></i>System Health</div>
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

            const tooltipStyle = { backgroundColor: '#1D1D1F', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 };
            const gridStyle = { color: 'rgba(0,0,0,0.04)' };

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
                        fill: true, tension: 0.4, borderWidth: 2,
                        pointRadius: 3, pointBackgroundColor: '#007AFF', pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipStyle, callbacks: { label: ctx => 'TZS ' + ctx.raw.toLocaleString() } } },
                    scales: { y: { beginAtZero: true, grid: gridStyle, ticks: { callback: v => (v/1000000).toFixed(1) + 'M' } }, x: { grid: { display: false } } }
                }
            });

            // Company Status Chart
            new Chart(document.getElementById('companyStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending', 'Rejected'],
                    datasets: [{ data: [{{ $companyStats['approved'] }}, {{ $companyStats['pending'] }}, {{ $companyStats['rejected'] ?? 0 }}], backgroundColor: ['#34C759', '#FF9500', '#FF3B30'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } } } }
            });

            // Customer Growth Chart
            new Chart(document.getElementById('customerGrowthChart'), {
                type: 'line',
                data: {
                    labels: @json($customerGrowth['labels']),
                    datasets: [{
                        label: 'New Customers',
                        data: @json($customerGrowth['values']),
                        borderColor: '#34C759',
                        backgroundColor: 'rgba(52, 199, 89, 0.08)',
                        fill: true, tension: 0.4, borderWidth: 2,
                        pointRadius: 2, pointBackgroundColor: '#34C759', pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: tooltipStyle },
                    scales: { y: { beginAtZero: true, grid: gridStyle, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
                }
            });

            // Customer Breakdown Doughnut
            new Chart(document.getElementById('customerBreakdownChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Registered', 'Walk-in'],
                    datasets: [{ data: [{{ $customerBreakdown['registered'] }}, {{ $customerBreakdown['walkin'] }}], backgroundColor: ['#007AFF', '#E5E5EA'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } } } }
            });

            // Active Companies Chart
            new Chart(document.getElementById('activeCompaniesChart'), {
                type: 'bar',
                data: {
                    labels: @json($activeCompanyTrend['labels']),
                    datasets: [{
                        label: 'Active Companies',
                        data: @json($activeCompanyTrend['values']),
                        backgroundColor: '#34C759',
                        borderRadius: 6, barThickness: 20
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: tooltipStyle },
                    scales: { y: { beginAtZero: true, grid: gridStyle, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
                }
            });

            // Company Aging Doughnut
            new Chart(document.getElementById('companyAgingChart'), {
                type: 'doughnut',
                data: {
                    labels: ['< 7 days', '7-30 days', '30-90 days', '90+ days'],
                    datasets: [{ data: [{{ $companyAging['last_7d'] }}, {{ $companyAging['last_30d'] }}, {{ $companyAging['last_90d'] }}, {{ $companyAging['older'] }}], backgroundColor: ['#34C759', '#007AFF', '#FF9500', '#5856D6'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 11 } } } } }
            });

            // Registration Chart
            new Chart(document.getElementById('registrationChart'), {
                type: 'bar',
                data: {
                    labels: @json($registrationTrends['labels']),
                    datasets: [{ data: @json($registrationTrends['values']), backgroundColor: '#007AFF', borderRadius: 6, barThickness: 16 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: gridStyle, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
                }
            });

            // User Role Chart
            new Chart(document.getElementById('userRoleChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Company Owners', 'Cashiers', 'Admins'],
                    datasets: [{ data: [{{ $usersByRole['company_owner'] ?? 0 }}, {{ $usersByRole['cashier'] ?? 0 }}, {{ $usersByRole['platform_admin'] ?? 0 }}], backgroundColor: ['#007AFF', '#34C759', '#5856D6'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } } }
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
