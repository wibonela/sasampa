<x-app-layout>
    <x-slot name="header">Edit Staff Member</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <h5 class="mb-3"><i class="bi bi-person me-2"></i>Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Account is active
                                </label>
                            </div>
                            <div class="form-text">Inactive users cannot log in.</div>
                        </div>

                        <hr class="my-4">

                        <!-- PIN Management -->
                        <h5 class="mb-3"><i class="bi bi-key me-2"></i>PIN Access</h5>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            @if($user->hasPin())
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>PIN is set</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>No PIN set</span>
                            @endif
                            <form action="{{ route('users.reset-pin', $user) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Generate a new PIN? The current PIN will be replaced.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-shuffle me-1"></i>{{ $user->hasPin() ? 'Reset PIN' : 'Generate PIN' }}
                                </button>
                            </form>
                        </div>

                        <hr class="my-4">

                        <!-- Branch Assignment -->
                        <h5 class="mb-3"><i class="bi bi-building me-2"></i>Branch Assignment</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assigned Branches <span class="text-danger">*</span></label>
                                @foreach($branches as $branch)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="branches[]"
                                               value="{{ $branch->id }}" id="branch_{{ $branch->id }}"
                                               {{ in_array($branch->id, old('branches', $userBranchIds)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="branch_{{ $branch->id }}">
                                            {{ $branch->name }}
                                            @if($branch->is_main)
                                                <span class="badge bg-primary">Main</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                                @error('branches')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="default_branch" class="form-label">Default Branch <span class="text-danger">*</span></label>
                                <select class="form-select @error('default_branch') is-invalid @enderror" name="default_branch" id="default_branch" required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('default_branch', $defaultBranchId) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('default_branch')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Permissions -->
                        <h5 class="mb-3"><i class="bi bi-shield-check me-2"></i>Permissions</h5>
                        <div class="row">
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <strong>{{ ucfirst($group) }}</strong>
                                        </div>
                                        <div class="card-body">
                                            @foreach($groupPermissions as $permission)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                                           value="{{ $permission->id }}" id="perm_{{ $permission->id }}"
                                                           {{ in_array($permission->id, old('permissions', $userPermissionIds)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Update Staff Member
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>

                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('Delete this staff member? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
