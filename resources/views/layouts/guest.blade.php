<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Prevent caching to avoid 419 errors -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>{{ config('app.name', 'Sasampa POS') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/favicon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background: var(--apple-gray-6); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div style="width: 100%; max-width: 400px; padding: 20px;">
        <div class="card" style="border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08);">
            <div class="card-body p-4">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <svg width="56" height="56" viewBox="0 0 32 32" style="margin-bottom: 12px;"><defs><linearGradient id="guestLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs><rect width="32" height="32" rx="6" fill="url(#guestLogoGrad)"/><rect x="8" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="20" width="16" height="4" rx="1" fill="#fff"/></svg>
                    <h4 style="font-weight: 600; margin: 0; color: var(--apple-text);">Sasampa POS</h4>
                    <p class="text-secondary mb-0" style="font-size: 13px;">Point of Sale System</p>
                </div>

                {{ $slot }}
            </div>
        </div>

        <p class="text-center text-secondary mt-3" style="font-size: 12px;">
            &copy; {{ date('Y') }} Sasampa POS. All rights reserved.
        </p>
    </div>

    <!-- Sanduku Feedback -->
    @include('components.sanduku')
</body>
</html>
