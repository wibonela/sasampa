<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">User Management</h1>
                <p class="page-subtitle">Manage all users across companies</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['verified'] }}</div>
                    <div class="stat-label">Verified</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-envelope-exclamation"></i>
                    </div>
                    <div class="stat-value">{{ $stats['unverified'] }}</div>
                    <div class="stat-label">Unverified</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-value">{{ $stats['pending_invitations'] }}</div>
                    <div class="stat-label">Pending Invites</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="bi bi-person-slash"></i>
                    </div>
                    <div class="stat-value">{{ $stats['inactive'] }}</div>
                    <div class="stat-label">Inactive</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-secondary mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary mb-1">Company</label>
                        <select name="company" class="form-select form-select-sm">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary mb-1">Verification</label>
                        <select name="verification" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="verified" {{ request('verification') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="unverified" {{ request('verification') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary mb-1">Invitation</label>
                        <select name="invitation" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="pending" {{ request('invitation') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="accepted" {{ request('invitation') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="expired" {{ request('invitation') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small text-secondary mb-1">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="company_owner" {{ request('role') == 'company_owner' ? 'selected' : '' }}>Owner</option>
                            <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        @if(request()->hasAny(['search', 'company', 'verification', 'invitation', 'role', 'status']))
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Company</th>
                            <th>Role</th>
                            <th>Email Status</th>
                            <th>Invitation</th>
                            <th>Account</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, {{ $user->isCompanyOwner() ? '#007AFF' : '#34C759' }} 0%, {{ $user->isCompanyOwner() ? '#5856D6' : '#30D158' }} 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 600;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 500;">{{ $user->name }}</div>
                                            <div class="text-secondary" style="font-size: 12px;">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->company)
                                        <a href="{{ route('admin.companies.show', $user->company) }}" class="text-decoration-none">
                                            {{ $user->company->name }}
                                        </a>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->isCompanyOwner())
                                        <span class="badge bg-primary">Owner</span>
                                    @else
                                        <span class="badge bg-secondary">Cashier</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Verified
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock me-1"></i>Unverified
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->invitation_accepted_at)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check me-1"></i>Accepted
                                        </span>
                                    @elseif($user->hasPendingInvitation())
                                        @if($user->isInvitationExpired())
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i>Expired
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-hourglass me-1"></i>Pending
                                            </span>
                                        @endif
                                    @elseif($user->invitation_method === 'pin')
                                        <span class="badge bg-info text-dark">
                                            <i class="bi bi-key me-1"></i>PIN Only
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-dash me-1"></i>None
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="card-footer">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
