<x-admin-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <a href="{{ route('admin.user-limit-requests.index') }}" class="btn btn-link text-secondary p-0 mb-2">
                    <i class="bi bi-arrow-left me-1"></i>Back to Requests
                </a>
                <h1 class="page-title">User Limit Request</h1>
                <p class="page-subtitle">Request from {{ $userLimitRequest->company->name }}</p>
            </div>
            <span class="badge bg-{{ $userLimitRequest->status_color }}" style="font-size: 14px; padding: 8px 16px;">
                {{ ucfirst($userLimitRequest->status) }}
            </span>
        </div>

        <div class="row g-4">
            <!-- Request Details -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Company</label>
                                    <div style="font-weight: 500; font-size: 18px;">{{ $userLimitRequest->company->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Requested By</label>
                                    <div style="font-weight: 500;">{{ $userLimitRequest->requester->name }}</div>
                                    <div style="font-size: 13px; color: var(--apple-gray-1);">{{ $userLimitRequest->requester->email }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Current User Limit</label>
                                    <div style="font-size: 24px; font-weight: 600;">{{ $userLimitRequest->current_limit }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Requested Limit</label>
                                    <div style="font-size: 24px; font-weight: 600; color: var(--apple-blue);">
                                        {{ $userLimitRequest->requested_limit }}
                                        <span style="font-size: 14px; color: var(--bs-success);">(+{{ $userLimitRequest->requested_limit - $userLimitRequest->current_limit }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Reason for Request</label>
                            <div class="p-3 rounded" style="background: var(--apple-gray-6);">
                                {{ $userLimitRequest->reason ?: 'No reason provided' }}
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Submitted</label>
                                <div>{{ $userLimitRequest->created_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                            @if($userLimitRequest->handled_at)
                                <div class="col-md-6">
                                    <label class="form-label text-secondary">Handled</label>
                                    <div>{{ $userLimitRequest->handled_at->format('M d, Y \a\t g:i A') }}</div>
                                    @if($userLimitRequest->handler)
                                        <div style="font-size: 13px; color: var(--apple-gray-1);">by {{ $userLimitRequest->handler->name }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($userLimitRequest->admin_notes)
                            <div class="mt-3">
                                <label class="form-label text-secondary">Admin Notes</label>
                                <div class="p-3 rounded" style="background: var(--apple-gray-6);">
                                    {{ $userLimitRequest->admin_notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Current Company Users -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Current Users ({{ $userLimitRequest->company->users->count() }})</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userLimitRequest->company->users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td class="text-secondary">{{ $user->email }}</td>
                                        <td>
                                            <span class="badge {{ $user->role === 'company_owner' ? 'bg-primary' : 'bg-secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="col-lg-4">
                @if($userLimitRequest->isPending())
                    <!-- Approve Form -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Approve Request</h5>
                        </div>
                        <form action="{{ route('admin.user-limit-requests.approve', $userLimitRequest) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Approved User Limit</label>
                                    <input type="number"
                                           name="approved_limit"
                                           class="form-control"
                                           value="{{ $userLimitRequest->requested_limit }}"
                                           min="{{ $userLimitRequest->current_limit }}"
                                           max="100">
                                    <div class="form-text">You can approve a different limit than requested</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Admin Notes (optional)</label>
                                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add any notes..."></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-1"></i>Approve Request
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Reject Form -->
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-x-circle me-2"></i>Reject Request</h5>
                        </div>
                        <form action="{{ route('admin.user-limit-requests.reject', $userLimitRequest) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                    <textarea name="admin_notes" class="form-control" rows="3" required placeholder="Explain why this request is being rejected..."></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-lg me-1"></i>Reject Request
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Already Handled -->
                    <div class="card">
                        <div class="card-body text-center py-4">
                            @if($userLimitRequest->status === 'approved')
                                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(40, 167, 69, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i class="bi bi-check-lg" style="color: var(--bs-success); font-size: 32px;"></i>
                                </div>
                                <h5 class="mt-3">Request Approved</h5>
                                <p class="text-secondary mb-0">
                                    Company limit updated to {{ $userLimitRequest->company->user_limit }} users
                                </p>
                            @else
                                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(220, 53, 69, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i class="bi bi-x-lg" style="color: var(--bs-danger); font-size: 32px;"></i>
                                </div>
                                <h5 class="mt-3">Request Rejected</h5>
                                <p class="text-secondary mb-0">This request was not approved</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
