<x-app-layout>
    <x-slot name="header">Add Staff Member</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <!-- Basic Information -->
                        <h5 class="mb-3"><i class="bi bi-person me-2"></i>Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required autofocus
                                       placeholder="John Doe">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" required
                                       placeholder="john@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <input type="hidden" name="role" value="cashier">

                        <hr class="my-4">

                        <!-- Access Method -->
                        <h5 class="mb-3"><i class="bi bi-key me-2"></i>Login Method</h5>
                        <div class="mb-3">
                            <label class="form-label">How will this user log in? <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check card p-3 h-100">
                                        <input class="form-check-input" type="radio" name="invitation_method" id="method_email" value="email"
                                               {{ old('invitation_method', 'email') === 'email' ? 'checked' : '' }} onchange="togglePinField()">
                                        <label class="form-check-label w-100" for="method_email">
                                            <strong><i class="bi bi-envelope me-1"></i>Email Only</strong>
                                            <p class="text-secondary small mb-0 mt-1">Send invitation email. User sets their own password.</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 h-100">
                                        <input class="form-check-input" type="radio" name="invitation_method" id="method_pin" value="pin"
                                               {{ old('invitation_method') === 'pin' ? 'checked' : '' }} onchange="togglePinField()">
                                        <label class="form-check-label w-100" for="method_pin">
                                            <strong><i class="bi bi-grid-3x3-gap me-1"></i>PIN Only</strong>
                                            <p class="text-secondary small mb-0 mt-1">Set PIN now. Share it verbally with the user.</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 h-100">
                                        <input class="form-check-input" type="radio" name="invitation_method" id="method_both" value="both"
                                               {{ old('invitation_method') === 'both' ? 'checked' : '' }} onchange="togglePinField()">
                                        <label class="form-check-label w-100" for="method_both">
                                            <strong><i class="bi bi-envelope me-1"></i><i class="bi bi-grid-3x3-gap"></i> Both</strong>
                                            <p class="text-secondary small mb-0 mt-1">Email invitation + PIN for quick access.</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('invitation_method')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="pin_field" style="display: none;">
                            <label for="pin" class="form-label">4-Digit PIN <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control @error('pin') is-invalid @enderror"
                                           id="pin" name="pin" value="{{ old('pin') }}"
                                           pattern="[0-9]{4}" maxlength="4" placeholder="0000">
                                    @error('pin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Write this down to share with the user.</div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-secondary w-100" onclick="generatePin()">
                                        <i class="bi bi-shuffle"></i> Generate
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Branch Assignment -->
                        <h5 class="mb-3"><i class="bi bi-building me-2"></i>Branch Assignment</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign to Branches <span class="text-danger">*</span></label>
                                @foreach($branches as $branch)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="branches[]"
                                               value="{{ $branch->id }}" id="branch_{{ $branch->id }}"
                                               {{ in_array($branch->id, old('branches', [])) ? 'checked' : '' }}>
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
                                    <option value="">Select default branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('default_branch') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('default_branch')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">This will be their primary working location.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Permissions -->
                        <h5 class="mb-3"><i class="bi bi-shield-check me-2"></i>Permissions</h5>
                        <p class="text-secondary small mb-3">Select what this user can do. Company owners always have all permissions.</p>

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
                                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                    @if($permission->description)
                                                        <div class="form-text small">{{ $permission->description }}</div>
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
                                <i class="bi bi-person-plus me-1"></i>Create Staff Member
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
        function togglePinField() {
            const method = document.querySelector('input[name="invitation_method"]:checked').value;
            const pinField = document.getElementById('pin_field');
            pinField.style.display = (method === 'pin' || method === 'both') ? 'block' : 'none';
        }

        function generatePin() {
            const pin = Math.floor(1000 + Math.random() * 9000).toString();
            document.getElementById('pin').value = pin;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', togglePinField);
    </script>
    @endpush
</x-app-layout>
