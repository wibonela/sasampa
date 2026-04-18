<x-app-layout>
    <div class="fade-in">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.companies.show', $company) }}" class="text-secondary" style="text-decoration: none;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="page-title mb-0">Edit Company</h1>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.companies.update', $company) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-building me-2"></i>Company Details
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $company->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Company Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $company->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Company Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $company->phone) }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $company->address) }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-person-badge me-2"></i>Owner Details
                        </div>
                        <div class="card-body">
                            @if($company->owner)
                                <div class="mb-3">
                                    <label class="form-label">Owner Name</label>
                                    <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror"
                                           value="{{ old('owner_name', $company->owner->name) }}">
                                    @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Owner Email</label>
                                    <input type="email" name="owner_email" class="form-control @error('owner_email') is-invalid @enderror"
                                           value="{{ old('owner_email', $company->owner->email) }}">
                                    @error('owner_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Changing the owner email will clear their verification and send a new verification email.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Owner Phone</label>
                                    <input type="text" name="owner_phone" class="form-control @error('owner_phone') is-invalid @enderror"
                                           value="{{ old('owner_phone', $company->owner->phone) }}">
                                    @error('owner_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @else
                                <p class="text-secondary mb-0">This company has no owner on record.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
