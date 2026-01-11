<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Get Started' }} - {{ config('app.name', 'Sasampa') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/favicon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --apple-blue: #007AFF;
            --apple-green: #34C759;
            --apple-orange: #FF9500;
            --apple-red: #FF3B30;
            --apple-text: #1D1D1F;
            --apple-text-secondary: #86868B;
            --apple-bg: #FFFFFF;
            --apple-bg-secondary: #F5F5F7;
            --apple-border: rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'SF Pro Display', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: var(--apple-bg-secondary);
            min-height: 100vh;
        }

        .onboarding-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navigation Bar */
        .onboarding-nav {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            border-bottom: 1px solid var(--apple-border);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            height: 52px;
        }

        .nav-content {
            max-width: 980px;
            margin: 0 auto;
            padding: 0 22px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--apple-text);
        }

        .nav-brand-icon {
            width: 32px;
            height: 32px;
            background: var(--apple-blue);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-brand-icon i {
            color: #fff;
            font-size: 16px;
        }

        .nav-brand-text {
            font-size: 17px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        .nav-link {
            font-size: 14px;
            color: var(--apple-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .nav-link:hover {
            text-decoration: underline;
        }

        /* Main Content */
        .onboarding-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 22px 60px;
        }

        .onboarding-container {
            width: 100%;
            max-width: 480px;
        }

        /* Progress Stepper */
        .progress-stepper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
            gap: 12px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-circle.pending {
            background: #E8E8ED;
            color: var(--apple-text-secondary);
        }

        .step-circle.active {
            background: var(--apple-blue);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 122, 255, 0.4);
        }

        .step-circle.completed {
            background: var(--apple-green);
            color: #fff;
        }

        .step-connector {
            width: 40px;
            height: 2px;
            background: #E8E8ED;
            border-radius: 1px;
        }

        .step-connector.completed {
            background: var(--apple-green);
        }

        /* Card */
        .onboarding-card {
            background: var(--apple-bg);
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08), 0 0 1px rgba(0, 0, 0, 0.08);
            padding: 44px 40px;
        }

        @media (max-width: 520px) {
            .onboarding-card {
                padding: 32px 24px;
                border-radius: 0;
                box-shadow: none;
                border-top: 1px solid var(--apple-border);
                border-bottom: 1px solid var(--apple-border);
            }

            .onboarding-main {
                padding: 80px 0 40px;
                align-items: flex-start;
            }

            .onboarding-container {
                max-width: 100%;
            }
        }

        /* Header */
        .onboarding-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .onboarding-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 20px;
            background: var(--apple-bg-secondary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .onboarding-icon i {
            font-size: 24px;
            color: var(--apple-blue);
        }

        .onboarding-icon.success {
            background: rgba(52, 199, 89, 0.12);
        }

        .onboarding-icon.success i {
            color: var(--apple-green);
        }

        .onboarding-icon.warning {
            background: rgba(255, 149, 0, 0.12);
        }

        .onboarding-icon.warning i {
            color: var(--apple-orange);
        }

        .onboarding-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--apple-text);
            margin: 0 0 8px;
            letter-spacing: -0.5px;
        }

        .onboarding-subtitle {
            font-size: 15px;
            color: var(--apple-text-secondary);
            margin: 0;
            line-height: 1.5;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--apple-text);
            margin-bottom: 8px;
        }

        .form-label .required {
            color: var(--apple-red);
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            font-size: 15px;
            font-family: inherit;
            color: var(--apple-text);
            background: var(--apple-bg);
            border: 1px solid #D2D2D7;
            border-radius: 10px;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--apple-blue);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
        }

        .form-control.is-invalid {
            border-color: var(--apple-red);
        }

        .form-control::placeholder {
            color: #C7C7CC;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .form-text {
            font-size: 12px;
            color: var(--apple-text-secondary);
            margin-top: 6px;
        }

        .invalid-feedback {
            font-size: 12px;
            color: var(--apple-red);
            margin-top: 6px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--apple-blue);
            color: #fff;
        }

        .btn-primary:hover {
            background: #0066d6;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background: var(--apple-bg-secondary);
            color: var(--apple-blue);
        }

        .btn-secondary:hover {
            background: #E8E8ED;
        }

        .btn-lg {
            padding: 14px 28px;
            font-size: 16px;
        }

        .btn-block {
            width: 100%;
        }

        /* Alert */
        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert i {
            font-size: 16px;
            margin-top: 1px;
        }

        .alert-success {
            background: rgba(52, 199, 89, 0.12);
            color: #1e7e34;
        }

        .alert-danger {
            background: rgba(255, 59, 48, 0.12);
            color: #c82333;
        }

        .alert ul {
            margin: 0;
            padding-left: 16px;
        }

        /* Logo Upload */
        .logo-upload-area {
            border: 2px dashed #D2D2D7;
            border-radius: 14px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--apple-bg-secondary);
        }

        .logo-upload-area:hover {
            border-color: var(--apple-blue);
            background: rgba(0, 122, 255, 0.04);
        }

        .logo-upload-area.has-preview {
            padding: 16px;
            background: var(--apple-bg);
        }

        .logo-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 12px;
            object-fit: contain;
        }

        .upload-icon {
            font-size: 32px;
            color: #C7C7CC;
            margin-bottom: 12px;
        }

        .upload-text {
            font-size: 14px;
            color: var(--apple-text-secondary);
            margin-bottom: 4px;
        }

        .upload-hint {
            font-size: 12px;
            color: #C7C7CC;
        }

        /* Info Box */
        .info-box {
            background: var(--apple-bg-secondary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .info-box-icon {
            width: 48px;
            height: 48px;
            background: rgba(0, 122, 255, 0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .info-box-icon i {
            font-size: 22px;
            color: var(--apple-blue);
        }

        /* List Group */
        .list-group {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--apple-border);
        }

        .list-group-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            background: var(--apple-bg);
            border-bottom: 1px solid var(--apple-border);
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .list-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .list-icon.blue { background: rgba(0, 122, 255, 0.12); }
        .list-icon.blue i { color: var(--apple-blue); }
        .list-icon.green { background: rgba(52, 199, 89, 0.12); }
        .list-icon.green i { color: var(--apple-green); }
        .list-icon.orange { background: rgba(255, 149, 0, 0.12); }
        .list-icon.orange i { color: var(--apple-orange); }

        .list-content strong {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--apple-text);
            margin-bottom: 2px;
        }

        .list-content span {
            font-size: 12px;
            color: var(--apple-text-secondary);
        }

        /* Divider */
        .divider {
            height: 1px;
            background: var(--apple-border);
            margin: 24px 0;
        }

        /* Text utilities */
        .text-center { text-align: center; }
        .text-muted { color: var(--apple-text-secondary); }
        .text-blue { color: var(--apple-blue); }
        .text-sm { font-size: 13px; }
        .mt-3 { margin-top: 16px; }
        .mt-4 { margin-top: 24px; }
        .mb-3 { margin-bottom: 16px; }
        .mb-4 { margin-bottom: 24px; }
        .d-none { display: none; }

        /* Footer */
        .onboarding-footer {
            text-align: center;
            padding: 24px 22px 32px;
        }

        .onboarding-footer p {
            font-size: 12px;
            color: var(--apple-text-secondary);
            margin: 0 0 4px;
        }

        .onboarding-footer a {
            color: var(--apple-blue);
            text-decoration: none;
        }

        .onboarding-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="onboarding-wrapper">
        <!-- Navigation -->
        <nav class="onboarding-nav">
            <div class="nav-content">
                <a href="{{ route('home') }}" class="nav-brand">
                    <div class="nav-brand-icon">
                        <i class="bi bi-shop"></i>
                    </div>
                    <span class="nav-brand-text">Sasampa</span>
                </a>
                @if(request()->routeIs('onboarding.step1'))
                    <a href="{{ route('login') }}" class="nav-link">Sign In</a>
                @endif
            </div>
        </nav>

        <!-- Main -->
        <main class="onboarding-main">
            <div class="onboarding-container">
                <!-- Progress Stepper -->
                @include('onboarding.partials.stepper', ['currentStep' => $currentStep ?? 1])

                <!-- Card -->
                <div class="onboarding-card">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="onboarding-footer">
            <p>&copy; {{ date('Y') }} Sasampa. All rights reserved.</p>
            <p><a href="https://sasampa.com">sasampa.com</a></p>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
