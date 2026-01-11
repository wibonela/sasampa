<x-onboarding-layout :currentStep="1" title="Create Account">
    <div class="onboarding-header">
        <div class="onboarding-icon">
            <i class="bi bi-person"></i>
        </div>
        <h1 class="onboarding-title">Create your account</h1>
        <p class="onboarding-subtitle">Enter your details to get started</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('onboarding.step1') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Full Name <span class="required">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                   name="name" value="{{ old('name') }}" required autofocus
                   placeholder="Enter your full name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Email Address <span class="required">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required
                   placeholder="you@example.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">We'll send a verification link to this email</div>
        </div>

        <div class="form-group">
            <label class="form-label">Password <span class="required">*</span></label>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   name="password" required
                   placeholder="Minimum 8 characters">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Confirm Password <span class="required">*</span></label>
            <input type="password" class="form-control"
                   name="password_confirmation" required
                   placeholder="Repeat your password">
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">
            Continue
            <i class="bi bi-arrow-right"></i>
        </button>

        <p class="text-center text-muted text-sm mt-4">
            Already have an account? <a href="{{ route('login') }}" class="text-blue">Sign in</a>
        </p>
    </form>
</x-onboarding-layout>
