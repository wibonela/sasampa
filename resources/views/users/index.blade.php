<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Staff Members</h1>
                <p class="page-subtitle">Manage your team and their permissions</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Add Staff
            </a>
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
                                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm mt-2">Add Your First Staff Member</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
