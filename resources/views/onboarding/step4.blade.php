<x-onboarding-layout :currentStep="4" title="Welcome">
    <div class="onboarding-header">
        <div class="onboarding-icon success">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="onboarding-title">You're all set!</h1>
        <p class="onboarding-subtitle">Welcome to Sasampa, {{ $user->name }}</p>
    </div>

    <!-- Business Summary -->
    <div class="info-box" style="display: flex; align-items: center; gap: 16px; text-align: left;">
        @if($company->logo)
            <img src="{{ Storage::url($company->logo) }}" alt="Logo"
                 style="width: 56px; height: 56px; border-radius: 12px; object-fit: contain; background: #fff; padding: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); flex-shrink: 0;">
        @else
            <div style="width: 56px; height: 56px; border-radius: 12px; background: var(--apple-blue); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="bi bi-building" style="font-size: 22px; color: #fff;"></i>
            </div>
        @endif
        <div>
            <strong style="display: block; font-size: 15px; color: var(--apple-text); margin-bottom: 2px;">{{ $company->name }}</strong>
            <span style="font-size: 13px; color: var(--apple-text-secondary);">{{ $company->email }}</span>
        </div>
    </div>

    <!-- Quick Start Guide -->
    <p style="font-size: 13px; font-weight: 600; color: var(--apple-text); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
        Quick Start
    </p>

    <div class="list-group mb-4">
        <div class="list-group-item">
            <div class="list-icon blue">
                <i class="bi bi-tags"></i>
            </div>
            <div class="list-content">
                <strong>Create Categories</strong>
                <span>Organize your products</span>
            </div>
        </div>
        <div class="list-group-item">
            <div class="list-icon green">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="list-content">
                <strong>Add Products</strong>
                <span>Build your inventory</span>
            </div>
        </div>
        <div class="list-group-item">
            <div class="list-icon orange">
                <i class="bi bi-cart-check"></i>
            </div>
            <div class="list-content">
                <strong>Start Selling</strong>
                <span>Use POS to make sales</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('onboarding.step4') }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg btn-block">
            Go to Dashboard
            <i class="bi bi-arrow-right"></i>
        </button>
    </form>

    <p class="text-center text-muted text-sm mt-4">
        Need help? Visit <a href="https://sasampa.com" target="_blank" class="text-blue">sasampa.com</a>
    </p>
</x-onboarding-layout>
