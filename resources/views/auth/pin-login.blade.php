<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h4">PIN Login</h2>
        <p class="text-secondary">Enter your email and PIN to sign in.</p>
    </div>

    @if($activeSession)
        <div class="alert alert-info mb-4">
            <i class="bi bi-person-check me-2"></i>
            Currently signed in: <strong>{{ $currentUser->name }}</strong>
            <form action="{{ route('pos.end-session') }}" method="POST" class="d-inline ms-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-link p-0">End Session</button>
            </form>
        </div>
    @endif

    <form method="POST" action="{{ route('pos.pin-login.submit') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="your@email.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- PIN -->
        <div class="mb-3">
            <label for="pin" class="form-label">4-Digit PIN</label>
            <input type="password" class="form-control @error('pin') is-invalid @enderror"
                   id="pin" name="pin" required
                   pattern="[0-9]{4}" maxlength="4" placeholder="****"
                   inputmode="numeric">
            @error('pin')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember this device</label>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In with PIN
            </button>
        </div>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-decoration-none">
            <i class="bi bi-envelope me-1"></i>Sign in with password instead
        </a>
    </div>
</x-guest-layout>
