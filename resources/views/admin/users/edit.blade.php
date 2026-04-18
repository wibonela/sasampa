<x-app-layout>
    <div class="fade-in">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.users.show', $user) }}" class="text-secondary" style="text-decoration: none;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="page-title mb-0">Edit User</h1>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-pencil-square me-2"></i>Contact Details
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.users.update', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Changing the email will clear verification and send a new verification email to the user.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle me-2"></i>Current Values
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-secondary" style="font-size: 12px;">Name</div>
                            <div class="fw-medium">{{ $user->name }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary" style="font-size: 12px;">Email</div>
                            <div class="fw-medium">{{ $user->email }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary" style="font-size: 12px;">Phone</div>
                            <div class="fw-medium">{{ $user->phone ?? '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary" style="font-size: 12px;">Email Verified</div>
                            <div class="fw-medium">
                                @if($user->email_verified_at)
                                    <span class="text-success"><i class="bi bi-check-circle me-1"></i>{{ $user->email_verified_at->format('M d, Y') }}</span>
                                @else
                                    <span class="text-warning"><i class="bi bi-clock me-1"></i>Not verified</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
