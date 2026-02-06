<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Mobile App Waitlist</h1>
                <p class="page-subtitle">Pre-launch signups for the Sasampa mobile app</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.waitlist.analytics') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-graph-up me-1"></i>Analytics
                </a>
                <a href="{{ route('admin.waitlist.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Signups</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="stat-value">{{ $stats['this_week'] }}</div>
                    <div class="stat-label">This Week</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-hourglass"></i>
                    </div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div class="stat-value">{{ $stats['contacted'] }}</div>
                    <div class="stat-label">Contacted</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon teal">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['converted'] }}</div>
                    <div class="stat-label">Converted</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="bi bi-phone"></i>
                    </div>
                    <div class="stat-value">{{ $stats['ios'] }} / {{ $stats['android'] }}</div>
                    <div class="stat-label">iOS / Android</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-secondary">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, phone, business..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            @foreach(\App\Models\MobileWaitlist::STATUSES as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Platform</label>
                        <select name="platform" class="form-select form-select-sm">
                            <option value="">All Platforms</option>
                            @foreach(\App\Models\MobileWaitlist::PLATFORMS as $value => $label)
                                <option value="{{ $value }}" {{ request('platform') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Business Type</label>
                        <select name="business_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            @foreach(\App\Models\MobileWaitlist::BUSINESS_TYPES as $value => $label)
                                <option value="{{ $value }}" {{ request('business_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                    </div>
                    <div class="col-md-1">
                        @if(request()->hasAny(['search', 'status', 'platform', 'business_type']))
                            <a href="{{ route('admin.waitlist.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Business</th>
                            <th>Phone</th>
                            <th>Platform</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td>
                                    <div style="font-weight: 500;">{{ $entry->name }}</div>
                                    @if($entry->email)
                                        <div class="text-secondary" style="font-size: 12px;">{{ $entry->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ Str::limit($entry->business_name, 25) }}</div>
                                    <div class="text-secondary" style="font-size: 12px;">{{ $entry->business_type_label }}</div>
                                </td>
                                <td>
                                    <a href="tel:{{ $entry->phone }}" class="text-decoration-none">{{ $entry->phone }}</a>
                                </td>
                                <td>
                                    @switch($entry->platform)
                                        @case('ios')
                                            <span class="badge bg-secondary"><i class="bi bi-apple me-1"></i>iOS</span>
                                            @break
                                        @case('android')
                                            <span class="badge bg-success"><i class="bi bi-android2 me-1"></i>Android</span>
                                            @break
                                        @default
                                            <span class="badge bg-primary">Both</span>
                                    @endswitch
                                </td>
                                <td>
                                    @switch($entry->status)
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('contacted')
                                            <span class="badge bg-info">Contacted</span>
                                            @break
                                        @case('converted')
                                            <span class="badge bg-success">Converted</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div style="font-size: 13px;">{{ $entry->created_at->format('M d, Y') }}</div>
                                    <div class="text-secondary" style="font-size: 11px;">{{ $entry->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('admin.waitlist.show', $entry) }}"><i class="bi bi-eye me-2"></i>View Details</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            @if($entry->status !== 'contacted')
                                                <li>
                                                    <form action="{{ route('admin.waitlist.update', $entry) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="contacted">
                                                        <button type="submit" class="dropdown-item"><i class="bi bi-telephone me-2"></i>Mark Contacted</button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if($entry->status !== 'converted')
                                                <li>
                                                    <form action="{{ route('admin.waitlist.update', $entry) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="converted">
                                                        <button type="submit" class="dropdown-item"><i class="bi bi-check-circle me-2"></i>Mark Converted</button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.waitlist.destroy', $entry) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <div class="mt-2">No waitlist entries yet</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($entries->hasPages())
                <div class="card-footer">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
