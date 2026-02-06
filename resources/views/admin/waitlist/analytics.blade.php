<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <a href="{{ route('admin.waitlist.index') }}" class="text-decoration-none text-secondary mb-2 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i>Back to Waitlist
                </a>
                <h1 class="page-title">Waitlist Analytics</h1>
                <p class="page-subtitle">Insights into mobile app waitlist signups</p>
            </div>
            <a href="{{ route('admin.waitlist.export') }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-download me-1"></i>Export All Data
            </a>
        </div>

        <!-- Key Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-value">{{ array_sum($statuses) }}</div>
                    <div class="stat-label">Total Signups</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div class="stat-value">{{ $conversionRate }}%</div>
                    <div class="stat-label">Conversion Rate</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <div class="stat-value">{{ array_sum(array_slice($signupsByDay, -7)) }}</div>
                    <div class="stat-label">Last 7 Days</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="stat-value">{{ count($signupsByDay) > 1 ? round(array_sum($signupsByDay) / count($signupsByDay), 1) : 0 }}</div>
                    <div class="stat-label">Avg/Day (30d)</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-graph-up me-2"></i>Signups Over Time (Last 30 Days)
                    </div>
                    <div class="card-body">
                        <canvas id="signupsChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-phone me-2"></i>Platform Preference
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="platformChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-building me-2"></i>Business Types
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="businessTypeChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-flag me-2"></i>Status Distribution
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="statusChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Launch Preparation -->
        <div class="card">
            <div class="card-header bg-primary bg-opacity-10">
                <i class="bi bi-rocket-takeoff me-2"></i>Launch Preparation
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="mb-2"><i class="bi bi-envelope me-2"></i>Email Campaign</h6>
                            <p class="text-secondary small mb-2">Export emails for launch announcement</p>
                            @php
                                $emailCount = \App\Models\MobileWaitlist::whereNotNull('email')->count();
                            @endphp
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">{{ $emailCount }} emails</span>
                                <a href="{{ route('admin.waitlist.export', ['has_email' => 1]) }}" class="btn btn-sm btn-outline-primary">Export</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="mb-2"><i class="bi bi-telephone me-2"></i>SMS Campaign</h6>
                            <p class="text-secondary small mb-2">Export phone numbers for SMS blast</p>
                            @php
                                $phoneCount = \App\Models\MobileWaitlist::count();
                            @endphp
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">{{ $phoneCount }} phones</span>
                                <a href="{{ route('admin.waitlist.export') }}" class="btn btn-sm btn-outline-primary">Export</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="mb-2"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Full Report</h6>
                            <p class="text-secondary small mb-2">Download complete waitlist data</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">{{ array_sum($statuses) }} entries</span>
                                <a href="{{ route('admin.waitlist.export') }}" class="btn btn-sm btn-outline-success">Download CSV</a>
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

            // Signups over time chart
            const signupsData = @json($signupsByDay);
            const labels = Object.keys(signupsData);
            const values = Object.values(signupsData);

            new Chart(document.getElementById('signupsChart'), {
                type: 'line',
                data: {
                    labels: labels.map(d => {
                        const date = new Date(d);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }),
                    datasets: [{
                        label: 'Signups',
                        data: values,
                        borderColor: '#007AFF',
                        backgroundColor: 'rgba(0, 122, 255, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#007AFF'
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

            // Platform chart
            const platformData = @json($platforms);
            new Chart(document.getElementById('platformChart'), {
                type: 'doughnut',
                data: {
                    labels: ['iOS', 'Android', 'Both'],
                    datasets: [{
                        data: [platformData['ios'] || 0, platformData['android'] || 0, platformData['both'] || 0],
                        backgroundColor: ['#5856D6', '#34C759', '#007AFF'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                    }
                }
            });

            // Business type chart
            const businessData = @json($businessTypes);
            const businessLabels = {
                'restaurant': 'Restaurant',
                'retail': 'Retail Shop',
                'pharmacy': 'Pharmacy',
                'supermarket': 'Supermarket',
                'salon': 'Salon',
                'other': 'Other'
            };
            new Chart(document.getElementById('businessTypeChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(businessData).map(k => businessLabels[k] || k),
                    datasets: [{
                        data: Object.values(businessData),
                        backgroundColor: ['#FF9500', '#34C759', '#007AFF', '#AF52DE', '#FF2D55', '#5856D6'],
                        borderRadius: 6,
                        barThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1 } },
                        y: { grid: { display: false } }
                    }
                }
            });

            // Status chart
            const statusData = @json($statuses);
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Contacted', 'Converted', 'Cancelled'],
                    datasets: [{
                        data: [statusData['pending'] || 0, statusData['contacted'] || 0, statusData['converted'] || 0, statusData['cancelled'] || 0],
                        backgroundColor: ['#FF9500', '#5AC8FA', '#34C759', '#FF3B30'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
