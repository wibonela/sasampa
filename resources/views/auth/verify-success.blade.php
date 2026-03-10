<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - Sasampa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fff;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .container {
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .check-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid #34c759;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        .check-icon svg { width: 40px; height: 40px; color: #34c759; }
        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        p {
            font-size: 15px;
            color: #6e6e73;
            margin-bottom: 32px;
            line-height: 1.5;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            margin-bottom: 12px;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #1a1a1a;
            color: #fff;
        }
        .btn-secondary {
            background: #fff;
            color: #1a1a1a;
            border: 1px solid #d1d1d6;
        }
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: #8e8e93;
            font-size: 13px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #d1d1d6;
        }
        .store-links {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 16px;
        }
        .store-links a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 1px solid #d1d1d6;
            border-radius: 10px;
            text-decoration: none;
            color: #1a1a1a;
            font-size: 13px;
            font-weight: 500;
        }
        .logo {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="check-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>

        <h1>Email Verified</h1>
        <p>
            Your email <strong>{{ $user->email }}</strong> has been verified.
            @if($needsOnboarding)
                Complete your business setup to get started.
            @else
                You can now access your dashboard.
            @endif
        </p>

        @if($needsOnboarding)
            <a href="{{ route('onboarding.step3') }}" class="btn btn-primary">
                Continue Setup in Browser
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                Open Dashboard
            </a>
        @endif

        <div class="divider">or</div>

        <p style="margin-bottom: 16px; font-size: 14px;">Have the Sasampa app installed? Open it to continue.</p>

        <div class="store-links">
            <a href="https://apps.apple.com/app/sasampa/id6743422756">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                App Store
            </a>
            <a href="https://play.google.com/store/apps/details?id=com.sasampa.sasampa_pos">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3.18 23.49c-.35-.35-.56-.89-.56-1.61V2.12c0-.72.21-1.26.56-1.61l.08-.08L14.49 11.7v.25L3.26 23.57l-.08-.08zm3.72-8.17l4.09-4.09 3.82 3.82-3.82 3.82-4.09-4.09zm7.91-4.09l-3.82 3.82-4.09-4.09 4.09-4.09 3.82 3.82.08.08-.08.46zm5.56-1.37c.63.36 1.07.98 1.07 1.82s-.44 1.47-1.07 1.82l-2.59 1.47-3.45-3.45v-.25l3.45-3.45 2.59 1.47v.57z"/></svg>
                Play Store
            </a>
        </div>
    </div>
</body>
</html>
