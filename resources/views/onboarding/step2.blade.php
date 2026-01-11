<x-onboarding-layout :currentStep="2" title="Verify Email">
    <div class="onboarding-header">
        <div class="onboarding-icon warning">
            <i class="bi bi-envelope"></i>
        </div>
        <h1 class="onboarding-title">Check your inbox</h1>
        <p class="onboarding-subtitle">We've sent a verification link to</p>
        <p style="font-weight: 600; color: var(--apple-blue); margin-top: 8px; font-size: 15px;">{{ $email }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            <div>A new verification link has been sent to your email address.</div>
        </div>
    @endif

    <div class="info-box">
        <div class="info-box-icon">
            <i class="bi bi-envelope-open"></i>
        </div>
        <p class="text-center text-muted text-sm" style="margin: 0; line-height: 1.6;">
            Click the link in your email to verify your account and continue setting up your business.
        </p>
    </div>

    <div style="background: rgba(255, 149, 0, 0.08); border-radius: 10px; padding: 14px 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
        <i class="bi bi-lightbulb" style="color: var(--apple-orange); font-size: 16px;"></i>
        <span style="color: #856404; font-size: 13px;">Don't forget to check your spam folder!</span>
    </div>

    <form method="POST" action="{{ route('onboarding.step2.resend') }}">
        @csrf
        <button type="submit" class="btn btn-secondary btn-lg btn-block">
            <i class="bi bi-arrow-clockwise"></i>
            Resend Verification Email
        </button>
    </form>

    <div class="divider"></div>

    <div class="text-center">
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" style="background: none; border: none; color: var(--apple-text-secondary); font-size: 13px; cursor: pointer; font-family: inherit;">
                <i class="bi bi-box-arrow-right"></i>
                Use a different email
            </button>
        </form>
    </div>
</x-onboarding-layout>
