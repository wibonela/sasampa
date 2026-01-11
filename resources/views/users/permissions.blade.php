<x-app-layout>
    <x-slot name="header">
        Manage Permissions: {{ $user->name }}
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- User Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person" style="color: var(--apple-blue); font-size: 24px;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $user->name }}</h5>
                            <span class="text-secondary">{{ $user->email }}</span>
                            <span class="badge {{ $user->role === 'company_owner' ? 'bg-primary' : 'bg-secondary' }} ms-2">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Permissions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.permissions.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            Select the features this user can access. Unchecked permissions will be restricted.
                        </div>

                        <div class="row">
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>{{ ucfirst($group) }}</strong>
                                                <button type="button" class="btn btn-sm btn-link p-0" onclick="toggleGroup('{{ $group }}')">
                                                    Select All
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach($groupPermissions as $permission)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input group-{{ $group }}" type="checkbox" name="permissions[]"
                                                           value="{{ $permission->id }}" id="perm_{{ $permission->id }}"
                                                           {{ in_array($permission->id, $userPermissionIds) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                    @if($permission->description)
                                                        <p class="text-secondary small mb-0">{{ $permission->description }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Save Permissions
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleGroup(group) {
            const checkboxes = document.querySelectorAll('.group-' + group);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        }
    </script>
    @endpush
</x-app-layout>
