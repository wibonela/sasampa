<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <a href="{{ route('admin.waitlist.index') }}" class="text-decoration-none text-secondary mb-2 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i>Back to Waitlist
                </a>
                <h1 class="page-title">{{ $waitlist->name }}</h1>
                <p class="page-subtitle">{{ $waitlist->business_name }}</p>
            </div>
            <div class="d-flex gap-2">
                @if($waitlist->status !== 'contacted')
                    <form action="{{ route('admin.waitlist.update', $waitlist) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="contacted">
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="bi bi-telephone me-1"></i>Mark Contacted
                        </button>
                    </form>
                @endif
                @if($waitlist->status !== 'converted')
                    <form action="{{ route('admin.waitlist.update', $waitlist) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="converted">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-check-circle me-1"></i>Mark Converted
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Info -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person me-2"></i>Contact Information
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="text-secondary small">Full Name</label>
                                <div class="fw-medium">{{ $waitlist->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-secondary small">Phone Number</label>
                                <div class="fw-medium">
                                    <a href="tel:{{ $waitlist->phone }}" class="text-decoration-none">{{ $waitlist->phone }}</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-secondary small">Email</label>
                                <div class="fw-medium">
                                    @if($waitlist->email)
                                        <a href="mailto:{{ $waitlist->email }}" class="text-decoration-none">{{ $waitlist->email }}</a>
                                    @else
                                        <span class="text-secondary">Not provided</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-secondary small">IP Address</label>
                                <div class="text-secondary">{{ $waitlist->ip_address ?? 'Unknown' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-building me-2"></i>Business Information
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="text-secondary small">Business Name</label>
                                <div class="fw-medium">{{ $waitlist->business_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-secondary small">Business Type</label>
                                <div class="fw-medium">{{ $waitlist->business_type_label }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-secondary small">Platform Preference</label>
                                <div class="fw-medium">
                                    @switch($waitlist->platform)
                                        @case('ios')
                                            <span class="badge bg-secondary"><i class="bi bi-apple me-1"></i>iOS</span>
                                            @break
                                        @case('android')
                                            <span class="badge bg-success"><i class="bi bi-android2 me-1"></i>Android</span>
                                            @break
                                        @default
                                            <span class="badge bg-primary">Both iOS & Android</span>
                                    @endswitch
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-secondary small">Referral Source</label>
                                <div class="text-secondary">{{ $waitlist->referral_source ?? 'Not specified' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-sticky me-2"></i>Admin Notes</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.waitlist.update', $waitlist) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <textarea name="notes" class="form-control" rows="4" placeholder="Add notes about this contact...">{{ $waitlist->notes }}</textarea>
                            <button type="submit" class="btn btn-primary btn-sm mt-3">
                                <i class="bi bi-save me-1"></i>Save Notes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle me-2"></i>Status
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="text-secondary small">Current Status</label>
                            <div class="mt-1">
                                @switch($waitlist->status)
                                    @case('pending')
                                        <span class="badge bg-warning fs-6">Pending</span>
                                        @break
                                    @case('contacted')
                                        <span class="badge bg-info fs-6">Contacted</span>
                                        @break
                                    @case('converted')
                                        <span class="badge bg-success fs-6">Converted</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-danger fs-6">Cancelled</span>
                                        @break
                                @endswitch
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-secondary small">Joined Waitlist</label>
                            <div class="fw-medium">{{ $waitlist->created_at->format('M d, Y \a\t H:i') }}</div>
                            <div class="text-secondary small">{{ $waitlist->created_at->diffForHumans() }}</div>
                        </div>

                        @if($waitlist->contacted_at)
                            <div class="mb-4">
                                <label class="text-secondary small">Contacted At</label>
                                <div class="fw-medium">{{ $waitlist->contacted_at->format('M d, Y \a\t H:i') }}</div>
                            </div>
                        @endif

                        @if($waitlist->converted_at)
                            <div class="mb-4">
                                <label class="text-secondary small">Converted At</label>
                                <div class="fw-medium">{{ $waitlist->converted_at->format('M d, Y \a\t H:i') }}</div>
                            </div>
                        @endif

                        <hr>

                        <form action="{{ route('admin.waitlist.update', $waitlist) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="form-label">Change Status</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach(\App\Models\MobileWaitlist::STATUSES as $value => $label)
                                    <option value="{{ $value }}" {{ $waitlist->status == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.waitlist.destroy', $waitlist) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this entry? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-trash me-1"></i>Delete Entry
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
