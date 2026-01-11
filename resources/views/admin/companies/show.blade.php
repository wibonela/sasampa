<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('admin.companies.index') }}" class="text-secondary" style="text-decoration: none;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h1 class="page-title mb-0">{{ $company->name }}</h1>
                    @if($company->status === 'approved')
                        <span class="badge bg-success">Approved</span>
                    @elseif($company->status === 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @else
                        <span class="badge bg-danger">Rejected</span>
                    @endif
                    @if($company->is_suspended ?? false)
                        <span class="badge bg-dark">Suspended</span>
                    @endif
                </div>
                <p class="page-subtitle">Company details and management</p>
            </div>
            <div class="d-flex gap-2">
                @if($company->status === 'pending')
                    <form action="{{ route('admin.companies.approve', $company) }}" method="POST"
                          data-confirm='{"title":"Approve Company","message":"This will approve {{ $company->name }} and they will be able to use the system.","type":"success","confirmText":"Approve"}'>
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-check-lg me-1"></i>Approve
                        </button>
                    </form>
                    <form action="{{ route('admin.companies.reject', $company) }}" method="POST"
                          data-confirm='{"title":"Reject Company","message":"Are you sure you want to reject {{ $company->name }}?","type":"danger","confirmText":"Reject"}'>
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-lg me-1"></i>Reject
                        </button>
                    </form>
                @elseif($company->status === 'approved')
                    @if($company->is_suspended ?? false)
                        <form action="{{ route('admin.companies.unsuspend', $company) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-play-fill me-1"></i>Unsuspend
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.companies.suspend', $company) }}" method="POST"
                              data-confirm='{"title":"Suspend Company","message":"This will prevent all users of {{ $company->name }} from accessing the system.","type":"warning","confirmText":"Suspend"}'>
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="bi bi-pause-fill me-1"></i>Suspend
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-value">{{ $company->users->count() }}/{{ $company->user_limit ?? 3 }}</div>
                    <div class="stat-label">Users</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-value">{{ number_format($company->products()->count()) }}</div>
                    <div class="stat-label">Products</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-value">{{ number_format($company->transactions()->count()) }}</div>
                    <div class="stat-label">Transactions</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stat-value">{{ number_format($company->transactions()->sum('total') / 1000, 0) }}K</div>
                    <div class="stat-label">Revenue (TZS)</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-building me-2"></i>Company Information
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Company Name</div>
                                <div style="font-weight: 500;">{{ $company->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Email</div>
                                <div style="font-weight: 500;">
                                    <a href="mailto:{{ $company->email }}" class="text-decoration-none">{{ $company->email }}</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Phone</div>
                                <div style="font-weight: 500;">
                                    @if($company->phone)
                                        <a href="tel:{{ $company->phone }}" class="text-decoration-none">{{ $company->phone }}</a>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Registered</div>
                                <div style="font-weight: 500;">{{ $company->created_at->format('M d, Y \a\t H:i') }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Address</div>
                                <div style="font-weight: 500;">{{ $company->address ?? '—' }}</div>
                            </div>
                            @if($company->approved_at)
                                <div class="col-md-6">
                                    <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Approved On</div>
                                    <div style="font-weight: 500;">{{ $company->approved_at->format('M d, Y \a\t H:i') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Users -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-people me-2"></i>Users ({{ $company->users->count() }}/{{ $company->user_limit ?? 3 }})</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($company->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, {{ $user->isCompanyOwner() ? '#007AFF' : '#34C759' }} 0%, {{ $user->isCompanyOwner() ? '#5856D6' : '#30D158' }} 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 600;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <span style="font-weight: 500;">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td class="text-secondary">{{ $user->email }}</td>
                                        <td>
                                            @if($user->isCompanyOwner())
                                                <span class="badge bg-primary">Owner</span>
                                            @else
                                                <span class="badge bg-secondary">Cashier</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->email_verified_at)
                                                <span class="badge bg-success">Verified</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary">{{ $user->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-secondary">No users yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-receipt me-2"></i>Recent Transactions
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Transaction #</th>
                                    <th>Cashier</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($company->transactions()->with('user')->latest()->limit(5)->get() as $transaction)
                                    <tr>
                                        <td class="fw-medium">{{ $transaction->transaction_number }}</td>
                                        <td class="text-secondary">{{ $transaction->user->name ?? '—' }}</td>
                                        <td class="text-end fw-medium text-success">{{ number_format($transaction->total) }} TZS</td>
                                        <td class="text-secondary">{{ $transaction->created_at->format('M d, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">No transactions yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Owner Card -->
                @if($company->owner)
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-person-badge me-2"></i>Company Owner
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #007AFF 0%, #5856D6 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; font-weight: 600;">
                                    {{ strtoupper(substr($company->owner->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; font-size: 16px;">{{ $company->owner->name }}</div>
                                    <div class="text-secondary" style="font-size: 13px;">{{ $company->owner->email }}</div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="mailto:{{ $company->owner->email }}" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-envelope me-1"></i>Email
                                </a>
                                @if($company->owner->phone)
                                    <a href="tel:{{ $company->owner->phone }}" class="btn btn-outline-secondary btn-sm flex-fill">
                                        <i class="bi bi-telephone me-1"></i>Call
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- User Limit Management -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-people me-2"></i>User Limit
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary">Current Limit</span>
                            <span class="fw-bold fs-4">{{ $company->user_limit ?? 3 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary">Users Active</span>
                            <span class="fw-medium">{{ $company->users->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary">Slots Available</span>
                            <span class="fw-medium text-{{ ($company->user_limit ?? 3) - $company->users->count() > 0 ? 'success' : 'danger' }}">
                                {{ max(0, ($company->user_limit ?? 3) - $company->users->count()) }}
                            </span>
                        </div>
                        <hr>
                        <form action="{{ route('admin.companies.update-limit', $company) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label class="form-label small text-secondary">Set New Limit</label>
                                <input type="number" name="user_limit" class="form-control" value="{{ $company->user_limit ?? 3 }}" min="1" max="100">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-check-lg me-1"></i>Update Limit
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Branches -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-shop me-2"></i>Branches ({{ $company->branches()->count() }})
                    </div>
                    <div class="card-body p-0">
                        @forelse($company->branches as $branch)
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <div>
                                    <div class="fw-medium">{{ $branch->name }}</div>
                                    <div class="text-secondary small">{{ $branch->address ?? 'No address' }}</div>
                                </div>
                                @if($branch->is_main)
                                    <span class="badge bg-primary">Main</span>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-4 text-secondary">No branches</div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="mailto:{{ $company->email }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-envelope me-2"></i>Send Email
                            </a>
                            @if($company->status === 'approved' && !($company->is_suspended ?? false))
                                <form action="{{ route('admin.companies.suspend', $company) }}" method="POST"
                                      data-confirm='{"title":"Suspend Company","message":"This will prevent all users from accessing the system.","type":"warning","confirmText":"Suspend"}'>
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                        <i class="bi bi-pause-circle me-2"></i>Suspend Company
                                    </button>
                                </form>
                            @endif
                            @if($company->status === 'rejected')
                                <form action="{{ route('admin.companies.approve', $company) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-check-circle me-2"></i>Approve Company
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
