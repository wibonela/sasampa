<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb small mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.mobile-access.index') }}">Mobile Access</a></li>
                        <li class="breadcrumb-item active">All Devices</li>
                    </ol>
                </nav>
                <h1 class="page-title">Registered Devices</h1>
                <p class="page-subtitle">All mobile devices across companies</p>
            </div>
            <a href="{{ route('admin.mobile-access.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Requests
            </a>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-phone"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Devices</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-phone-fill"></i>
                    </div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                    <div class="stat-label">Active</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon gray">
                        <i class="bi bi-phone"></i>
                    </div>
                    <div class="stat-value">{{ $stats['inactive'] }}</div>
                    <div class="stat-label">Inactive</div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" class="d-flex align-items-center gap-3">
                    <span class="text-secondary" style="font-size: 13px;">Filter</span>
                    <select name="active" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                        <option value="">All Devices</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active Only</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                    @if(request()->hasAny(['active', 'company_id']))
                        <a href="{{ route('admin.mobile-access.devices') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Company</th>
                            <th>User</th>
                            <th>App Version</th>
                            <th>Last Active</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                        <tr>
                            <td>
                                <div style="font-weight: 500;">{{ $device->device_name ?: 'Unknown Device' }}</div>
                                <div class="text-secondary" style="font-size: 12px;">
                                    {{ $device->device_model ?: 'Unknown' }} &bull; {{ $device->os_version ?: 'Unknown' }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $device->company->name }}</div>
                            </td>
                            <td>
                                <div>{{ $device->user->name }}</div>
                                <div class="text-secondary" style="font-size: 12px;">{{ $device->user->email }}</div>
                            </td>
                            <td>{{ $device->app_version ?: '-' }}</td>
                            <td>
                                @if($device->last_active_at)
                                    <div>{{ $device->last_active_at->format('M d, Y') }}</div>
                                    <div class="text-secondary" style="font-size: 12px;">{{ $device->last_active_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-secondary">Never</span>
                                @endif
                            </td>
                            <td>
                                @if($device->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($device->is_active)
                                    <form action="{{ route('admin.mobile-access.devices.deactivate', $device) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.mobile-access.devices.activate', $device) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                No devices found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($devices->hasPages())
                <div class="card-footer">
                    {{ $devices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
