<x-app-layout>
    <!-- Toast Notification -->
    <div id="toast-notification" class="toast-notification" style="display: none;">
        <div class="toast-content">
            <i class="bi bi-check-circle-fill toast-icon"></i>
            <span class="toast-message">Saved successfully!</span>
        </div>
    </div>

    <style>
        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            animation: slideDown 0.3s ease-out;
        }
        .toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #34C759 0%, #30B350 100%);
            color: white;
            padding: 14px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(52, 199, 89, 0.35);
            font-weight: 500;
            font-size: 15px;
        }
        .toast-icon {
            font-size: 20px;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
            to {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
        }
    </style>

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

    @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('toast-notification');
                const message = document.querySelector('.toast-message');

                // Set appropriate message
                @if (session('status') === 'profile-updated')
                    message.textContent = 'Profile saved successfully!';
                @elseif (session('status') === 'password-updated')
                    message.textContent = 'Password updated successfully!';
                @endif

                // Show toast
                toast.style.display = 'block';

                // Hide after 3 seconds with animation
                setTimeout(function() {
                    toast.style.animation = 'fadeOut 0.3s ease-out forwards';
                    setTimeout(function() {
                        toast.style.display = 'none';
                    }, 300);
                }, 3000);
            });
        </script>
    @endif
</x-app-layout>
