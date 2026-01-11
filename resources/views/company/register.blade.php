<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Register Your Business - {{ config('app.name', 'Sasampa') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 1rem;
        }
        .register-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
            padding: 2rem;
            width: 100%;
            max-width: 600px;
        }
        .register-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .register-logo i {
            font-size: 3rem;
            color: #667eea;
        }
        .section-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #667eea;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="register-card">
            <div class="register-logo">
                <svg width="56" height="56" viewBox="0 0 32 32"><defs><linearGradient id="regLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs><rect width="32" height="32" rx="6" fill="url(#regLogoGrad)"/><rect x="8" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="20" width="16" height="4" rx="1" fill="#fff"/></svg>
                <h3 class="mt-2 mb-0">Register Your Business</h3>
                <small class="text-muted">Start using Sasampa POS today</small>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('company.register') }}">
                @csrf

                <!-- Business Information -->
                <div class="section-title">
                    <i class="bi bi-building me-2"></i>Business Information
                </div>

                <div class="mb-3">
                    <label for="company_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                           id="company_name" name="company_name" value="{{ old('company_name') }}" required>
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="company_email" class="form-label">Business Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('company_email') is-invalid @enderror"
                               id="company_email" name="company_email" value="{{ old('company_email') }}" required>
                        @error('company_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="company_phone" class="form-label">Business Phone</label>
                        <input type="text" class="form-control @error('company_phone') is-invalid @enderror"
                               id="company_phone" name="company_phone" value="{{ old('company_phone') }}">
                        @error('company_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="company_address" class="form-label">Business Address</label>
                    <textarea class="form-control @error('company_address') is-invalid @enderror"
                              id="company_address" name="company_address" rows="2">{{ old('company_address') }}</textarea>
                    @error('company_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Owner Account -->
                <div class="section-title">
                    <i class="bi bi-person me-2"></i>Owner Account
                </div>

                <div class="mb-3">
                    <label for="owner_name" class="form-label">Your Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('owner_name') is-invalid @enderror"
                           id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required>
                    @error('owner_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="owner_email" class="form-label">Your Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('owner_email') is-invalid @enderror"
                           id="owner_email" name="owner_email" value="{{ old('owner_email') }}" required>
                    @error('owner_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">You'll use this email to log in</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Register Business
                    </button>
                </div>

                <div class="text-center mt-3">
                    <span class="text-muted">Already have an account?</span>
                    <a href="{{ route('login') }}" class="text-decoration-none">Log in</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
