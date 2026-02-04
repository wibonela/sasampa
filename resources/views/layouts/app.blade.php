<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sasampa POS') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/favicon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Mobile Header -->
    <header class="mobile-header">
        <div class="mobile-header-title">
            <svg width="24" height="24" viewBox="0 0 32 32" style="vertical-align: middle; margin-right: 6px;"><defs><linearGradient id="logoGrad1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs><rect width="32" height="32" rx="6" fill="url(#logoGrad1)"/><rect x="8" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="20" width="16" height="4" rx="1" fill="#fff"/></svg>
            Sasampa
        </div>
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
    </header>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <button class="sidebar-close" onclick="toggleSidebar()">
            <i class="bi bi-x"></i>
        </button>
        <div class="sidebar-header">
            <h4>
                <svg width="28" height="28" viewBox="0 0 32 32" style="vertical-align: middle; margin-right: 8px;"><defs><linearGradient id="logoGrad2" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs><rect width="32" height="32" rx="6" fill="url(#logoGrad2)"/><rect x="8" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="20" width="16" height="4" rx="1" fill="#fff"/></svg>
                Sasampa POS
            </h4>
            @if(auth()->user()->company)
                <small>{{ auth()->user()->company->name }}</small>
            @elseif(auth()->user()->isPlatformAdmin())
                <small>Platform Admin</small>
            @endif
        </div>

        <div class="sidebar-nav">
            @if(auth()->user()->isPlatformAdmin())
                <div class="nav-section">
                    <p class="nav-section-title">Platform</p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-grid"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}" href="{{ route('admin.companies.index') }}">
                                <i class="bi bi-building"></i>
                                Companies
                                @php $pendingCount = \App\Models\Company::where('status', 'pending')->count(); @endphp
                                @if($pendingCount > 0)
                                    <span class="badge bg-warning">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                                <i class="bi bi-bell"></i>
                                Notifications
                                @php $unreadCount = \App\Models\AdminNotification::whereNull('read_at')->count(); @endphp
                                @if($unreadCount > 0)
                                    <span class="badge bg-danger">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sanduku.*') ? 'active' : '' }}" href="{{ route('admin.sanduku.index') }}">
                                <i class="bi bi-inbox"></i>
                                Sanduku Feedback
                                @php $newFeedback = \App\Models\Sanduku::where('status', 'new')->count(); @endphp
                                @if($newFeedback > 0)
                                    <span class="badge bg-info">{{ $newFeedback }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.user-limit-requests.*') ? 'active' : '' }}" href="{{ route('admin.user-limit-requests.index') }}">
                                <i class="bi bi-people"></i>
                                User Limits
                                @php $pendingRequests = \App\Models\UserLimitRequest::where('status', 'pending')->count(); @endphp
                                @if($pendingRequests > 0)
                                    <span class="badge bg-warning">{{ $pendingRequests }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-person-gear"></i>
                                User Management
                                @php
                                    $usersNeedingAttention = \App\Models\User::whereNotNull('company_id')
                                        ->where(function($q) {
                                            $q->whereNull('email_verified_at')
                                              ->orWhere(function($q2) {
                                                  $q2->whereNotNull('invitation_token')
                                                     ->whereNull('invitation_accepted_at');
                                              });
                                        })->count();
                                @endphp
                                @if($usersNeedingAttention > 0)
                                    <span class="badge bg-warning">{{ $usersNeedingAttention }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.mobile-access.*') ? 'active' : '' }}" href="{{ route('admin.mobile-access.index') }}">
                                <i class="bi bi-phone"></i>
                                Mobile Access
                                @php $pendingMobileRequests = \App\Models\MobileAppRequest::where('status', 'pending')->count(); @endphp
                                @if($pendingMobileRequests > 0)
                                    <span class="badge bg-warning">{{ $pendingMobileRequests }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <p class="nav-section-title">Documentation</p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.documentation.categories.*') ? 'active' : '' }}" href="{{ route('admin.documentation.categories.index') }}">
                                <i class="bi bi-folder"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.documentation.articles.*') ? 'active' : '' }}" href="{{ route('admin.documentation.articles.index') }}">
                                <i class="bi bi-file-text"></i>
                                Articles
                            </a>
                        </li>
                    </ul>
                </div>
            @else
                <!-- Branch Switcher -->
                @include('components.branch-switcher')

                <div class="nav-section">
                    <p class="nav-section-title">Menu</p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-grid"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                                <i class="bi bi-cart3"></i>
                                Point of Sale
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <p class="nav-section-title">Inventory</p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                                <i class="bi bi-box-seam"></i>
                                Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                <i class="bi bi-tag"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                                <i class="bi bi-clipboard-data"></i>
                                Stock
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <p class="nav-section-title">Matumizi (Expenses)</p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('expenses.*') && !request()->routeIs('expenses.summary') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                                <i class="bi bi-wallet2"></i>
                                Expenses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('expense-categories.*') ? 'active' : '' }}" href="{{ route('expense-categories.index') }}">
                                <i class="bi bi-folder"></i>
                                Categories
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <p class="nav-section-title">Reports</p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                                <i class="bi bi-receipt"></i>
                                Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                <i class="bi bi-bar-chart"></i>
                                Reports
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <p class="nav-section-title">Analytics</p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('analytics.profit') && !request()->routeIs('analytics.profit.*') ? 'active' : '' }}" href="{{ route('analytics.profit') }}">
                                <i class="bi bi-calculator"></i>
                                Profit Analysis
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('analytics.profit.branches') ? 'active' : '' }}" href="{{ route('analytics.profit.branches') }}">
                                <i class="bi bi-building"></i>
                                By Branch
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('analytics.profit.trends') ? 'active' : '' }}" href="{{ route('analytics.profit.trends') }}">
                                <i class="bi bi-graph-up"></i>
                                Trends
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="nav-section">
                    <p class="nav-section-title">Settings</p>
                    <ul class="nav flex-column">
                        @if(auth()->user()->hasPermission('manage_users'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <i class="bi bi-people"></i>
                                    Users
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_branches'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}" href="{{ route('branches.index') }}">
                                    <i class="bi bi-building"></i>
                                    Branches
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermission('manage_settings'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                                    <i class="bi bi-gear"></i>
                                    Settings
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="nav-section">
                    <p class="nav-section-title">Help</p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('docs.*') ? 'active' : '' }}" href="{{ route('docs.index') }}" target="_blank">
                                <i class="bi bi-book"></i>
                                Documentation
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
        </div>

        <!-- User -->
        <div class="sidebar-user">
            <div class="user-info">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="user-details">
                    <p class="user-name">{{ auth()->user()->name }}</p>
                    <p class="user-role">
                        @if(auth()->user()->isPlatformAdmin())
                            Admin
                        @elseif(auth()->user()->isCompanyOwner())
                            Owner
                        @else
                            Cashier
                        @endif
                    </p>
                </div>
            </div>
            <div class="user-actions">
                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-secondary">Profile</a>
                <form method="POST" action="{{ route('logout') }}" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Sign Out</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{ $slot }}
    </div>

    <!-- Custom Confirm Modal -->
    <div class="custom-modal-overlay" id="confirmModal">
        <div class="custom-modal">
            <div class="custom-modal-body">
                <div class="custom-modal-icon" id="confirmModalIcon">
                    <i class="bi bi-question-circle"></i>
                </div>
                <h3 class="custom-modal-title" id="confirmModalTitle">Confirm Action</h3>
                <p class="custom-modal-message" id="confirmModalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="custom-modal-actions">
                <button type="button" class="modal-btn cancel" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="modal-btn confirm" id="confirmModalBtn" onclick="confirmAction()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Close sidebar when clicking a link (on mobile)
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    toggleSidebar();
                }
            });
        });

        // Custom Confirm Modal
        let confirmCallback = null;
        let confirmForm = null;

        function showConfirmModal(options) {
            const modal = document.getElementById('confirmModal');
            const icon = document.getElementById('confirmModalIcon');
            const title = document.getElementById('confirmModalTitle');
            const message = document.getElementById('confirmModalMessage');
            const confirmBtn = document.getElementById('confirmModalBtn');

            // Set content
            title.textContent = options.title || 'Confirm Action';
            message.textContent = options.message || 'Are you sure you want to proceed?';
            confirmBtn.textContent = options.confirmText || 'Confirm';

            // Set icon and colors
            icon.className = 'custom-modal-icon ' + (options.type || 'info');
            confirmBtn.className = 'modal-btn confirm ' + (options.type || '');

            // Set icon
            const iconMap = {
                'danger': 'bi-exclamation-triangle',
                'warning': 'bi-exclamation-circle',
                'success': 'bi-check-circle',
                'info': 'bi-question-circle'
            };
            icon.innerHTML = '<i class="bi ' + (iconMap[options.type] || iconMap.info) + '"></i>';

            // Store callback or form
            confirmCallback = options.onConfirm || null;
            confirmForm = options.form || null;

            // Show modal
            modal.classList.add('show');
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('show');
            confirmCallback = null;
            confirmForm = null;
        }

        function confirmAction() {
            if (confirmForm) {
                confirmForm.submit();
            } else if (confirmCallback) {
                confirmCallback();
            }
            closeConfirmModal();
        }

        // Override form submissions with data-confirm attribute
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[data-confirm]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const confirmData = JSON.parse(this.dataset.confirm);
                    showConfirmModal({
                        title: confirmData.title,
                        message: confirmData.message,
                        type: confirmData.type || 'warning',
                        confirmText: confirmData.confirmText || 'Confirm',
                        form: this
                    });
                });
            });

            // Handle buttons with data-confirm
            document.querySelectorAll('button[data-confirm], a[data-confirm]').forEach(el => {
                el.addEventListener('click', function(e) {
                    if (this.tagName === 'A') {
                        e.preventDefault();
                        const confirmData = JSON.parse(this.dataset.confirm);
                        const href = this.href;
                        showConfirmModal({
                            title: confirmData.title,
                            message: confirmData.message,
                            type: confirmData.type || 'warning',
                            confirmText: confirmData.confirmText || 'Confirm',
                            onConfirm: () => { window.location.href = href; }
                        });
                    }
                });
            });
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeConfirmModal();
            }
        });
    </script>

    @stack('scripts')

    <!-- Sanduku Feedback -->
    @include('components.sanduku')
</body>
</html>
