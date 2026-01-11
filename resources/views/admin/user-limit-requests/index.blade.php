<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">User Limit Requests</h1>
                <p class="page-subtitle">Review and manage company user limit requests</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="{{ route('admin.user-limit-requests.index', ['status' => 'pending']) }}" class="card text-decoration-none {{ $status === 'pending' ? 'border-warning' : '' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 193, 7, 0.1); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-clock" style="color: var(--bs-warning); font-size: 24px;"></i>
                            </div>
                            <div>
                                <div style="font-size: 14px; color: var(--apple-gray-1);">Pending</div>
                                <div style="font-size: 24px; font-weight: 600;">{{ $stats['pending'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.user-limit-requests.index', ['status' => 'approved']) }}" class="card text-decoration-none {{ $status === 'approved' ? 'border-success' : '' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(40, 167, 69, 0.1); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-check-circle" style="color: var(--bs-success); font-size: 24px;"></i>
                            </div>
                            <div>
                                <div style="font-size: 14px; color: var(--apple-gray-1);">Approved</div>
                                <div style="font-size: 24px; font-weight: 600;">{{ $stats['approved'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.user-limit-requests.index', ['status' => 'rejected']) }}" class="card text-decoration-none {{ $status === 'rejected' ? 'border-danger' : '' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(220, 53, 69, 0.1); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-x-circle" style="color: var(--bs-danger); font-size: 24px;"></i>
                            </div>
                            <div>
                                <div style="font-size: 14px; color: var(--apple-gray-1);">Rejected</div>
                                <div style="font-size: 24px; font-weight: 600;">{{ $stats['rejected'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.user-limit-requests.index', ['status' => 'all']) }}"
                       class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        All
                    </a>
                    <a href="{{ route('admin.user-limit-requests.index', ['status' => 'pending']) }}"
                       class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">
                        Pending
                    </a>
                    <a href="{{ route('admin.user-limit-requests.index', ['status' => 'approved']) }}"
                       class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">
                        Approved
                    </a>
                    <a href="{{ route('admin.user-limit-requests.index', ['status' => 'rejected']) }}"
                       class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">
                        Rejected
                    </a>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Requested By</th>
                            <th>Current Limit</th>
                            <th>Requested</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="width: 120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-building" style="color: var(--apple-blue); font-size: 18px;"></i>
                                        </div>
                                        <div>
                                            <span style="font-weight: 500;">{{ $request->company->name }}</span>
                                            <div style="font-size: 12px; color: var(--apple-gray-1);">
                                                {{ $request->company->getUserCount() }} users currently
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary">{{ $request->requester->name }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $request->current_limit }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $request->requested_limit }}</span>
                                    <span class="text-success">(+{{ $request->requested_limit - $request->current_limit }})</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $request->status_color }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="text-secondary">
                                    {{ $request->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.user-limit-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No {{ $status !== 'all' ? $status : '' }} requests found</p>
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
