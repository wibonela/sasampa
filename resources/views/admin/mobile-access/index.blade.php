<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Mobile Access Requests</h1>
                <p class="page-subtitle">Manage mobile app access for companies</p>
            </div>
            <a href="{{ route('admin.mobile-access.devices') }}" class="btn btn-outline-primary">
                <i class="bi bi-phone me-1"></i> View All Devices
            </a>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['approved'] }}</div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['rejected'] }}</div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon gray">
                        <i class="bi bi-slash-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['revoked'] }}</div>
                    <div class="stat-label">Revoked</div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" class="d-flex align-items-center gap-3">
                    <span class="text-secondary" style="font-size: 13px;">Filter</span>
                    <select name="status" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                        <option value="">All Requests</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
                    @if(request('status'))
                        <a href="{{ route('admin.mobile-access.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                            <th>Expected Devices</th>
                            <th>Requested</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td>
                                    <div style="font-weight: 500;">{{ $request->company->name }}</div>
                                    <div class="text-secondary" style="font-size: 12px;">{{ $request->company->email }}</div>
                                </td>
                                <td>
                                    @if($request->company->owner)
                                        <div>{{ $request->company->owner->name }}</div>
                                        <div class="text-secondary" style="font-size: 12px;">{{ $request->company->owner->email }}</div>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $request->expected_devices }} devices</span>
                                </td>
                                <td>
                                    <div>{{ $request->created_at->format('M d, Y') }}</div>
                                    <div class="text-secondary" style="font-size: 12px;">{{ $request->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($request->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($request->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">Revoked</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($request->status === 'pending')
                                        <form action="{{ route('admin.mobile-access.approve', $request) }}" method="POST" class="d-inline"
                                              data-confirm='{"title":"Approve Mobile Access","message":"Approve mobile access for {{ $request->company->name }}?","type":"success","confirmText":"Approve"}'>
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                            Reject
                                        </button>
                                    @elseif($request->status === 'approved')
                                        <a href="{{ route('admin.mobile-access.show', $request) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                                data-bs-toggle="modal" data-bs-target="#revokeModal{{ $request->id }}">
                                            Revoke
                                        </button>
                                    @else
                                        <a href="{{ route('admin.mobile-access.show', $request) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    @endif
                                </td>
                            </tr>

                            @if($request->status === 'pending')
                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.mobile-access.reject', $request) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Mobile Access</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Reject mobile access request for <strong>{{ $request->company->name }}</strong>?</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Reason for rejection</label>
                                                    <textarea name="reason" class="form-control" rows="3" required
                                                              placeholder="Please provide a reason for rejection..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($request->status === 'approved')
                            <!-- Revoke Modal -->
                            <div class="modal fade" id="revokeModal{{ $request->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.mobile-access.revoke', $request) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Revoke Mobile Access</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    This will revoke mobile access and deactivate all registered devices for this company.
                                                </div>
                                                <p>Revoke mobile access for <strong>{{ $request->company->name }}</strong>?</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Reason for revocation</label>
                                                    <textarea name="reason" class="form-control" rows="3" required
                                                              placeholder="Please provide a reason for revocation..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Revoke Access</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    No mobile access requests found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="card-footer">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
