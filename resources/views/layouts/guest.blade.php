<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sasampa POS') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background: var(--apple-gray-6); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div style="width: 100%; max-width: 400px; padding: 20px;">
        <div class="card" style="border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08);">
            <div class="card-body p-4">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, var(--apple-blue) 0%, #5856D6 100%); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                        <i class="bi bi-shop" style="font-size: 24px; color: #fff;"></i>
                    </div>
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
