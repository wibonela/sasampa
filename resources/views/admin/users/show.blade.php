<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('admin.users.index') }}" class="text-secondary" style="text-decoration: none;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h1 class="page-title mb-0">{{ $user->name }}</h1>
                    @if($user->isCompanyOwner())
                        <span class="badge bg-primary">Owner</span>
                    @else
                        <span class="badge bg-secondary">Cashier</span>
                    @endif
                    @if(!$user->is_active)
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>
                <p class="page-subtitle">{{ $user->email }}</p>
            </div>
        </div>

        <!-- Status Diagnosis Banner -->
        @if(!$diagnosis['can_login'])
            <div class="alert alert-warning mb-4">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle" style="font-size: 1.2rem;"></i>
                    <div>
                        <strong>User cannot log in</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @if(!$diagnosis['company_approved'])
                                <li>Company is not approved</li>
                            @endif
                            @if(!$diagnosis['email_verified'])
                                <li>Email is not verified</li>
                            @endif
                            @if(!$diagnosis['is_active'])
                                <li>Account is deactivated</li>
                            @endif
                            @if(!$diagnosis['invitation_accepted'] && $user->invitation_method !== 'pin')
                                <li>Invitation not accepted
                                    @if($diagnosis['invitation_expired'])
                                        <span class="text-danger">(expired)</span>
                                    @endif
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- User Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-person me-2"></i>User Information
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Full Name</div>
                                <div style="font-weight: 500;">{{ $user->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Email Address</div>
                                <div style="font-weight: 500;">
                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Role</div>
                                <div style="font-weight: 500;">
                                    @if($user->isCompanyOwner())
                                        Company Owner
                                    @else
                                        Cashier
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Company</div>
                                <div style="font-weight: 500;">
                                    @if($user->company)
                                        <a href="{{ route('admin.companies.show', $user->company) }}" class="text-decoration-none">
                                            {{ $user->company->name }}
                                        </a>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Invitation Method</div>
                                <div style="font-weight: 500;">
                                    @if($user->invitation_method === 'email')
                                        <i class="bi bi-envelope me-1"></i>Email Only
                                    @elseif($user->invitation_method === 'pin')
                                        <i class="bi bi-key me-1"></i>PIN Only
                                    @elseif($user->invitation_method === 'both')
                                        <i class="bi bi-envelope me-1"></i><i class="bi bi-key me-1"></i>Email & PIN
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">PIN Status</div>
                                <div style="font-weight: 500;">
                                    @if($user->hasPin())
                                        <span class="text-success"><i class="bi bi-check-circle me-1"></i>PIN Set</span>
                                    @else
                                        <span class="text-secondary"><i class="bi bi-x-circle me-1"></i>No PIN</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Created</div>
                                <div style="font-weight: 500;">{{ $user->created_at->format('M d, Y \a\t H:i') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Last Updated</div>
                                <div style="font-weight: 500;">{{ $user->updated_at->format('M d, Y \a\t H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification & Invitation Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-shield-check me-2"></i>Verification & Invitation Status
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Email Verified</div>
                                <div style="font-weight: 500;">
                                    @if($user->email_verified_at)
                                        <span class="text-success">
                                            <i class="bi bi-check-circle me-1"></i>Yes
                                        </span>
                                        <div class="text-secondary small">{{ $user->email_verified_at->format('M d, Y \a\t H:i') }}</div>
                                    @else
                                        <span class="text-warning">
                                            <i class="bi bi-clock me-1"></i>No
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Invitation Status</div>
                                <div style="font-weight: 500;">
                                    @if($user->invitation_accepted_at)
                                        <span class="text-success">
                                            <i class="bi bi-check-circle me-1"></i>Accepted
                                        </span>
                                        <div class="text-secondary small">{{ $user->invitation_accepted_at->format('M d, Y \a\t H:i') }}</div>
                                    @elseif($user->hasPendingInvitation())
                                        @if($user->isInvitationExpired())
                                            <span class="text-danger">
                                                <i class="bi bi-x-circle me-1"></i>Expired
                                            </span>
                                        @else
                                            <span class="text-warning">
                                                <i class="bi bi-hourglass me-1"></i>Pending
                                            </span>
                                        @endif
                                        @if($user->invitation_sent_at)
                                            <div class="text-secondary small">Sent {{ $user->invitation_sent_at->format('M d, Y \a\t H:i') }}</div>
                                        @endif
                                    @elseif($user->invitation_method === 'pin')
                                        <span class="text-info">
                                            <i class="bi bi-key me-1"></i>PIN-only user (no invitation needed)
                                        </span>
                                    @else
                                        <span class="text-secondary">
                                            <i class="bi bi-dash me-1"></i>No invitation sent
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($user->invitation_sent_at && $user->hasPendingInvitation())
                                <div class="col-md-6">
                                    <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Invitation Sent</div>
                                    <div style="font-weight: 500;">
                                        {{ $user->invitation_sent_at->format('M d, Y \a\t H:i') }}
                                        <div class="text-secondary small">{{ $user->invitation_sent_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-secondary" style="font-size: 12px; margin-bottom: 4px;">Invitation Expires</div>
                                    <div style="font-weight: 500;">
                                        @php $expiresAt = $user->invitation_sent_at->addDays(2); @endphp
                                        @if($expiresAt->isPast())
                                            <span class="text-danger">
                                                {{ $expiresAt->format('M d, Y \a\t H:i') }}
                                                <div class="small">(expired {{ $expiresAt->diffForHumans() }})</div>
                                            </span>
                                        @else
                                            {{ $expiresAt->format('M d, Y \a\t H:i') }}
                                            <div class="text-secondary small">{{ $expiresAt->diffForHumans() }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Assigned Branches -->
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-building me-2"></i>Assigned Branches ({{ $user->branches->count() }})
                    </div>
                    <div class="card-body p-0">
                        @forelse($user->branches as $branch)
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <div>
                                    <div class="fw-medium">{{ $branch->name }}</div>
                                    <div class="text-secondary small">{{ $branch->address ?? 'No address' }}</div>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($branch->pivot->is_default)
                                        <span class="badge bg-primary">Default</span>
                                    @endif
                                    @if($branch->is_main)
                                        <span class="badge bg-info">Main</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-secondary">
                                <i class="bi bi-building"></i>
                                <p class="mb-0 mt-2">No branches assigned</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Diagnosis -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-clipboard-check me-2"></i>Status Checklist
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Company exists</span>
                                @if($diagnosis['has_company'])
                                    <i class="bi bi-check-circle text-success"></i>
                                @else
                                    <i class="bi bi-x-circle text-danger"></i>
                                @endif
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Company approved</span>
                                @if($diagnosis['company_approved'])
                                    <i class="bi bi-check-circle text-success"></i>
                                @else
                                    <i class="bi bi-x-circle text-danger"></i>
                                @endif
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Email verified</span>
                                @if($diagnosis['email_verified'])
                                    <i class="bi bi-check-circle text-success"></i>
                                @else
                                    <i class="bi bi-x-circle text-warning"></i>
                                @endif
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Invitation accepted</span>
                                @if($diagnosis['invitation_accepted'] || $user->invitation_method === 'pin')
                                    <i class="bi bi-check-circle text-success"></i>
                                @elseif($diagnosis['invitation_expired'])
                                    <i class="bi bi-x-circle text-danger"></i>
                                @elseif($diagnosis['invitation_pending'])
                                    <i class="bi bi-hourglass text-warning"></i>
                                @else
                                    <i class="bi bi-dash-circle text-secondary"></i>
                                @endif
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Account active</span>
                                @if($diagnosis['is_active'])
                                    <i class="bi bi-check-circle text-success"></i>
                                @else
                                    <i class="bi bi-x-circle text-danger"></i>
                                @endif
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Has branches</span>
                                @if($diagnosis['has_branches'])
                                    <i class="bi bi-check-circle text-success"></i>
                                @else
                                    <i class="bi bi-x-circle text-warning"></i>
                                @endif
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-top-2">
                                <strong>Can log in</strong>
                                @if($diagnosis['can_login'])
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <!-- Email Verification Actions -->
                            @if(!$user->email_verified_at)
                                <form action="{{ route('admin.users.verify-email', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-check-circle me-2"></i>Verify Email Manually
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.resend-verification', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-envelope me-2"></i>Resend Verification Email
                                    </button>
                                </form>
                            @endif

                            <!-- Invitation Actions -->
                            @if($user->hasPendingInvitation())
                                <form action="{{ route('admin.users.regenerate-invitation', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-arrow-repeat me-2"></i>Regenerate Invitation
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.force-accept-invitation', $user) }}" method="POST"
                                      data-confirm='{"title":"Force Accept Invitation","message":"This will mark the invitation as accepted without user action. Use only when necessary.","type":"warning","confirmText":"Accept"}'>
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm w-100">
                                        <i class="bi bi-check-all me-2"></i>Force Accept Invitation
                                    </button>
                                </form>
                            @elseif(!$user->invitation_accepted_at && $user->invitation_method !== 'pin')
                                <form action="{{ route('admin.users.regenerate-invitation', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-send me-2"></i>Send Invitation Email
                                    </button>
                                </form>
                            @endif

                            <hr class="my-2">

                            <!-- Password Reset -->
                            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="bi bi-key me-2"></i>Send Password Reset Link
                                </button>
                            </form>

                            <!-- PIN Reset -->
                            @if($user->hasPin())
                                <form action="{{ route('admin.users.reset-pin', $user) }}" method="POST"
                                      data-confirm='{"title":"Reset PIN","message":"This will generate a new random PIN. Share it securely with the user.","type":"warning","confirmText":"Reset PIN"}'>
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="bi bi-123 me-2"></i>Reset PIN
                                    </button>
                                </form>
                            @endif

                            <hr class="my-2">

                            <!-- Activate/Deactivate -->
                            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST"
                                  @if($user->is_active)
                                      data-confirm='{"title":"Deactivate User","message":"This will prevent {{ $user->name }} from logging in.","type":"danger","confirmText":"Deactivate"}'
                                  @endif
                            >
                                @csrf
                                @method('PATCH')
                                @if($user->is_active)
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="bi bi-person-slash me-2"></i>Deactivate User
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-person-check me-2"></i>Activate User
                                    </button>
                                @endif
                            </form>

                            <hr class="my-2">

                            <!-- Contact -->
                            <a href="mailto:{{ $user->email }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-envelope me-2"></i>Send Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
