<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">Branches</a></li>
                        <li class="breadcrumb-item active">{{ $branch->name }}</li>
                    </ol>
                </nav>
                <h1 class="page-title">Branch Users</h1>
                <p class="page-subtitle">Manage users assigned to {{ $branch->name }}</p>
            </div>
            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Branches
            </a>
        </div>

        <div class="row">
            <!-- Assign User Form -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-plus me-1"></i>Assign User
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($availableUsers->count() > 0)
                            <form action="{{ route('branches.assign-user', $branch) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Select User</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror"
                                            id="user_id" name="user_id" required>
                                        <option value="">Choose a user...</option>
                                        @foreach($availableUsers as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->name }} ({{ $user->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1">
                                    <label class="form-check-label" for="is_default">
                                        Set as user's default branch
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle me-1"></i>Assign User
                                </button>
                            </form>
                        @else
                            <p class="text-secondary mb-0">All users are already assigned to this branch.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Assigned Users List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people me-1"></i>Assigned Users
                            <span class="badge bg-secondary ms-1">{{ $branch->users->count() }}</span>
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Default</th>
                                    <th style="width: 100px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branch->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-person" style="color: var(--apple-blue);"></i>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 500;">{{ $user->name }}</div>
                                                    <div class="text-secondary small">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->role === 'company_owner')
                                                <span class="badge bg-primary">Owner</span>
                                            @elseif($user->role === 'cashier')
                                                <span class="badge bg-info">Cashier</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $user->role }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->pivot->is_default)
                                                <span class="badge bg-success">Default</span>
                                            @else
                                                <form action="{{ route('branches.set-default', [$branch, $user->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link btn-sm text-secondary p-0">
                                                        Set as default
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            @unless($user->id === auth()->id() && auth()->user()->branches()->count() <= 1)
                                                <form action="{{ route('branches.remove-user', [$branch, $user->id]) }}" method="POST"
                                                      onsubmit="return confirm('Remove this user from the branch?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">
                                            No users assigned to this branch yet.
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
</x-app-layout>
