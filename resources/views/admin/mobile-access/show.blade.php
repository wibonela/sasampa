<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb small mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.mobile-access.index') }}">Mobile Access</a></li>
                        <li class="breadcrumb-item active">{{ $request->company->name }}</li>
                    </ol>
                </nav>
                <h1 class="page-title">{{ $request->company->name }}</h1>
                <p class="page-subtitle">Mobile access request details</p>
            </div>
            <a href="{{ route('admin.mobile-access.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row g-4">
            <!-- Request Details -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Request Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 35%;" class="text-secondary">Status</th>
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
                            </tr>
                            <tr>
                                <th class="text-secondary">Requested</th>
                                <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Expected Devices</th>
                                <td>{{ $request->expected_devices }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Request Reason</th>
                                <td>{{ $request->request_reason ?: '-' }}</td>
                            </tr>
                            @if($request->approved_at)
                            <tr>
                                <th class="text-secondary">Approved</th>
                                <td>{{ $request->approved_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endif
                            @if($request->rejected_at)
                            <tr>
                                <th class="text-secondary">Rejected</th>
                                <td>{{ $request->rejected_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Rejection Reason</th>
                                <td class="text-danger">{{ $request->rejection_reason }}</td>
                            </tr>
                            @endif
                            @if($request->revoked_at)
                            <tr>
                                <th class="text-secondary">Revoked</th>
                                <td>{{ $request->revoked_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Revocation Reason</th>
                                <td class="text-danger">{{ $request->revocation_reason }}</td>
                            </tr>
                            @endif
                            @if($request->reviewer)
                            <tr>
                                <th class="text-secondary">Reviewed By</th>
                                <td>{{ $request->reviewer->name }}</td>
                            </tr>
                            @endif
                        </table>

                        @if($request->status === 'pending')
                        <div class="d-flex gap-2 mt-3">
                            <form action="{{ route('admin.mobile-access.approve', $request) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg me-1"></i> Approve
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                Reject
                            </button>
                        </div>
                        @elseif($request->status === 'approved')
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#revokeModal">
                                <i class="bi bi-slash-circle me-1"></i> Revoke Access
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Company Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 35%;" class="text-secondary">Company Name</th>
                                <td>{{ $request->company->name }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Email</th>
                                <td>{{ $request->company->email }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Phone</th>
                                <td>{{ $request->company->phone ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Owner</th>
                                <td>
                                    @if($request->company->owner)
                                        {{ $request->company->owner->name }}<br>
                                        <small class="text-secondary">{{ $request->company->owner->email }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Total Users</th>
                                <td>{{ $request->company->users->count() }}</td>
                            </tr>
                            <tr>
                                <th class="text-secondary">Company Status</th>
                                <td>
                                    @if($request->company->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-warning">{{ ucfirst($request->company->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Registered Devices -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Registered Devices</h5>
                        <span class="badge bg-primary">{{ $devices->count() }} devices</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Device</th>
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
                                            {{ $device->device_model }} &bull; {{ $device->os_version }}
                                        </div>
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
                                    <td colspan="6" class="text-center py-4 text-secondary">
                                        No devices registered yet
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($request->status === 'pending')
    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
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
    <div class="modal fade" id="revokeModal" tabindex="-1">
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
</x-app-layout>
