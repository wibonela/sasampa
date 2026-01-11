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
                </div>
                <p class="page-subtitle">Company details</p>
            </div>
            @if($company->status === 'pending')
                <div class="d-flex gap-2">
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
                </div>
            @endif
        </div>

        <div class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Company Information</div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Company Name</div>
                                <div style="font-weight: 500;">{{ $company->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Email</div>
                                <div style="font-weight: 500;">{{ $company->email }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Phone</div>
                                <div style="font-weight: 500;">{{ $company->phone ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Registered</div>
                                <div style="font-weight: 500;">{{ $company->created_at->format('M d, Y') }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Address</div>
                                <div style="font-weight: 500;">{{ $company->address ?? '—' }}</div>
                            </div>
                            @if($company->approved_at)
                                <div class="col-md-6">
                                    <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Approved On</div>
                                    <div style="font-weight: 500;">{{ $company->approved_at->format('M d, Y') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Users -->
                <div class="card mt-4">
                    <div class="card-header">Users ({{ $company->users->count() }})</div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($company->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, var(--apple-blue) 0%, #5856D6 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 11px; font-weight: 500;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <span style="font-weight: 500;">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->isCompanyOwner())
                                                <span class="badge bg-primary">Owner</span>
                                            @else
                                                <span class="badge bg-secondary">Cashier</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary">{{ $user->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">No users yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stats Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Statistics</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--apple-border) !important;">
                            <span class="text-secondary" style="font-size: 13px;">Users</span>
                            <span style="font-weight: 600;">{{ $company->users->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--apple-border) !important;">
                            <span class="text-secondary" style="font-size: 13px;">Products</span>
                            <span style="font-weight: 600;">{{ $company->products()->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--apple-border) !important;">
                            <span class="text-secondary" style="font-size: 13px;">Transactions</span>
                            <span style="font-weight: 600;">{{ number_format($company->transactions()->count()) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-secondary" style="font-size: 13px;">Total Revenue</span>
                            <span style="font-weight: 600; color: var(--apple-green);">
                                {{ number_format($company->transactions()->sum('total')) }} TZS
                            </span>
                        </div>
                    </div>
                </div>

                @if($company->owner)
                    <div class="card mt-4">
                        <div class="card-header">Owner</div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--apple-blue) 0%, #5856D6 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; font-weight: 500;">
                                    {{ strtoupper(substr($company->owner->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600;">{{ $company->owner->name }}</div>
                                    <div class="text-secondary" style="font-size: 13px;">{{ $company->owner->email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
