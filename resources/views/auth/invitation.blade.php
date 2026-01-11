<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h4">Welcome, {{ $user->name }}</h2>
        <p class="text-secondary">Complete your account setup to get started.</p>
    </div>

    <form method="POST" action="{{ route('invitation.accept', $token) }}">
        @csrf

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Create Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" required autofocus
                   placeholder="Enter your password">
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
                   placeholder="Confirm your password">
        </div>

        @if($requiresPin && !$user->hasPin())
            <hr class="my-4">

            <!-- PIN -->
            <div class="mb-3">
                <label for="pin" class="form-label">Set Your PIN</label>
                <input type="text" class="form-control @error('pin') is-invalid @enderror"
                       id="pin" name="pin" required
                       pattern="[0-9]{4}" maxlength="4" placeholder="4-digit PIN"
                       inputmode="numeric">
                @error('pin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Use this PIN for quick access at the point of sale.</div>
            </div>

            <!-- Confirm PIN -->
            <div class="mb-3">
                <label for="pin_confirmation" class="form-label">Confirm PIN</label>
                <input type="text" class="form-control"
                       id="pin_confirmation" name="pin_confirmation" required
                       pattern="[0-9]{4}" maxlength="4" placeholder="Confirm PIN"
                       inputmode="numeric">
            </div>
        @endif

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle me-2"></i>Activate Account
            </button>
        </div>
    </form>

    <p class="text-center text-secondary small mt-4">
        By activating your account, you agree to access the system responsibly.
    </p>
</x-guest-layout>
