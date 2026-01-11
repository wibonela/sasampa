<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Staff Members</h1>
                <p class="page-subtitle">Manage your team and their permissions</p>
            </div>
            @if($canCreateMore)
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Add Staff
                </a>
            @else
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#requestMoreModal">
                    <i class="bi bi-person-plus me-1"></i>Add Staff
                </button>
            @endif
        </div>

        <!-- User Limit Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-people" style="color: var(--apple-blue); font-size: 24px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 14px; color: var(--apple-gray-1);">User Slots</div>
                            <div style="font-size: 24px; font-weight: 600;">{{ $userCount }} / {{ $userLimit }}</div>
                        </div>
                    </div>
                    <div class="text-end">
                        @if($canCreateMore)
                            <span class="badge bg-success">{{ $userLimit - $userCount }} slots available</span>
                        @elseif($hasPendingRequest)
                            <span class="badge bg-warning">Request pending</span>
                        @else
                            <span class="badge bg-danger">Limit reached</span>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#requestMoreModal">
                                Request More
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Branches</th>
                            <th>Status</th>
                            <th style="width: 150px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-person" style="color: var(--apple-blue); font-size: 18px;"></i>
                                        </div>
                                        <div>
                                            <span style="font-weight: 500;">{{ $user->name }}</span>
                                            @if($user->hasPin())
                                                <span class="badge bg-info ms-1" title="PIN enabled"><i class="bi bi-key"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'company_owner' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->branches->count() > 0)
                                        @foreach($user->branches->take(2) as $branch)
                                            <span class="badge bg-light text-dark">{{ $branch->name }}</span>
                                        @endforeach
                                        @if($user->branches->count() > 2)
                                            <span class="badge bg-light text-dark">+{{ $user->branches->count() - 2 }}</span>
                                        @endif
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->hasPendingInvitation())
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($user->hasPendingInvitation())
                                            <form action="{{ route('users.resend-invitation', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Resend Invitation">
                                                    <i class="bi bi-envelope"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('users.permissions', $user) }}" class="btn btn-sm btn-outline-secondary" title="Permissions">
                                            <i class="bi bi-shield-check"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('users.toggle-active', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi {{ $user->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    <i class="bi bi-people" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No staff members yet</p>
                                    @if($canCreateMore)
                                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm mt-2">Add Your First Staff Member</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Request More Users Modal -->
    <div class="modal fade" id="requestMoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope me-2"></i>Request More User Slots
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('users.request-more') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($hasPendingRequest)
                            <div class="alert alert-warning">
                                <i class="bi bi-clock me-2"></i>
                                You already have a pending request. Please wait for admin to review it.
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Your current limit is <strong>{{ $userLimit }} users</strong>. Request more slots by filling out this form.
                            </div>

                            <div class="mb-3">
                                <label class="form-label">How many user slots do you need?</label>
                                <select name="requested_limit" class="form-select" required>
                                    @for($i = $userLimit + 1; $i <= min($userLimit + 20, 50); $i++)
                                        <option value="{{ $i }}">{{ $i }} users (+{{ $i - $userLimit }} more)</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason for request</label>
                                <textarea name="reason" class="form-control" rows="3" required
                                          placeholder="Please explain why you need additional user slots..."></textarea>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        @if(!$hasPendingRequest)
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>Submit Request
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
