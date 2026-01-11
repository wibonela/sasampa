<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="page-title">Profile</h1>
            <p class="page-subtitle">Manage your account settings</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Profile Information -->
                <div class="card mb-4">
                    <div class="card-header">Profile Information</div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Update Password -->
                <div class="card mb-4">
                    <div class="card-header">Update Password</div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="card" style="border-color: rgba(255, 59, 48, 0.2);">
                    <div class="card-header" style="color: var(--apple-red);">Danger Zone</div>
                    <div class="card-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--apple-blue) 0%, #5856D6 100%); display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 32px; font-weight: 500; margin-bottom: 16px;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <h5 style="font-weight: 600; margin-bottom: 4px;">{{ auth()->user()->name }}</h5>
                        <p class="text-secondary mb-3" style="font-size: 13px;">{{ auth()->user()->email }}</p>
                        <span class="badge bg-primary">
                            @if(auth()->user()->isPlatformAdmin())
                                Platform Admin
                            @elseif(auth()->user()->isCompanyOwner())
                                Company Owner
                            @else
                                Cashier
                            @endif
                        </span>
                    </div>
                </div>

                @if(auth()->user()->company)
                    <div class="card mt-4">
                        <div class="card-header">Company</div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-building" style="color: var(--apple-blue);"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;">{{ auth()->user()->company->name }}</div>
                                    <div class="text-secondary" style="font-size: 12px;">
                                        Member since {{ auth()->user()->created_at->format('M Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
