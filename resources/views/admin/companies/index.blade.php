<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Companies</h1>
                <p class="page-subtitle">Manage registered businesses</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['approved'] }}</div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Companies</div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" class="d-flex align-items-center gap-3">
                    <span class="text-secondary" style="font-size: 13px;">Filter</span>
                    <select name="status" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                        <option value="">All Companies</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @if(request('status'))
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Owner</th>
                            <th>Contact</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            <tr>
                                <td>
                                    <div style="font-weight: 500;">{{ $company->name }}</div>
                                    @if($company->address)
                                        <div class="text-secondary" style="font-size: 12px;">{{ Str::limit($company->address, 35) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($company->owner)
                                        <div>{{ $company->owner->name }}</div>
                                        <div class="text-secondary" style="font-size: 12px;">{{ $company->owner->email }}</div>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $company->email }}</div>
                                    @if($company->phone)
                                        <div class="text-secondary" style="font-size: 12px;">{{ $company->phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $company->created_at->format('M d, Y') }}</div>
                                    <div class="text-secondary" style="font-size: 12px;">{{ $company->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    @if($company->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($company->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($company->status === 'pending')
                                        <form action="{{ route('admin.companies.approve', $company) }}" method="POST" class="d-inline"
                                              data-confirm='{"title":"Approve Company","message":"Approve {{ $company->name }}?","type":"success","confirmText":"Approve"}'>
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.companies.reject', $company) }}" method="POST" class="d-inline ms-1"
                                              data-confirm='{"title":"Reject Company","message":"Reject {{ $company->name }}?","type":"danger","confirmText":"Reject"}'>
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    No companies found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($companies->hasPages())
                <div class="card-footer">
                    {{ $companies->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
