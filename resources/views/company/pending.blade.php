<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pending Approval - {{ config('app.name', 'Sasampa') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .pending-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 1rem;
        }
        .pending-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        .pending-icon {
            font-size: 5rem;
            color: #ffc107;
            margin-bottom: 1.5rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.85rem;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="pending-wrapper">
        <div class="pending-card">
            @if($company->isRejected())
                <div class="pending-icon text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
                <h2 class="mb-3">Application Rejected</h2>
                <span class="status-badge status-rejected mb-4 d-inline-block">Rejected</span>
                <p class="text-muted mb-4">
                    Unfortunately, your business registration for <strong>{{ $company->name }}</strong> was not approved.
                    Please contact support for more information.
                </p>
            @else
                <div class="pending-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <h2 class="mb-3">Pending Approval</h2>
                <span class="status-badge status-pending mb-4 d-inline-block">Under Review</span>
                <p class="text-muted mb-4">
                    Your business <strong>{{ $company->name }}</strong> is currently being reviewed.
                    We'll notify you once your account is approved.
                </p>
            @endif

            <div class="bg-light rounded p-3 mb-4">
                <div class="row text-start">
                    <div class="col-6 text-muted">Business:</div>
                    <div class="col-6 fw-semibold">{{ $company->name }}</div>
                    <div class="col-6 text-muted">Email:</div>
                    <div class="col-6">{{ $company->email }}</div>
                    <div class="col-6 text-muted">Submitted:</div>
                    <div class="col-6">{{ $company->created_at->format('M d, Y') }}</div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-house me-2"></i>Back to Home
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted w-100">
                        <i class="bi bi-box-arrow-right me-2"></i>Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
