<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h4">Reset Password</h2>
        <p class="text-secondary">Enter your new password below.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" required autocomplete="new-password"
                   placeholder="Enter new password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Minimum 8 characters.</div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control"
                   id="password_confirmation" name="password_confirmation" required
                   autocomplete="new-password" placeholder="Confirm new password">
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-key me-2"></i>Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>
