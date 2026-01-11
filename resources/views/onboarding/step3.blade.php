<x-onboarding-layout :currentStep="3" title="Business Details">
    <div class="onboarding-header">
        <div class="onboarding-icon">
            <i class="bi bi-building"></i>
        </div>
        <h1 class="onboarding-title">Business details</h1>
        <p class="onboarding-subtitle">Tell us about your business</p>
    </div>

    @if(session('verified'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            <div>Email verified successfully! Now let's set up your business.</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('onboarding.step3') }}" enctype="multipart/form-data">
        @csrf

        <!-- Logo Upload -->
        <div class="form-group">
            <label class="form-label">Business Logo <span style="color: var(--apple-text-secondary); font-weight: 400;">(Optional)</span></label>
            <input type="file" name="logo" id="logoInput" accept="image/*" style="display: none;">
            <div class="logo-upload-area" id="logoUploadArea" onclick="document.getElementById('logoInput').click()">
                <div id="logoPlaceholder">
                    <div class="upload-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <p class="upload-text">Click to upload your logo</p>
                    <p class="upload-hint">PNG, JPG up to 2MB</p>
                </div>
                <img id="logoPreview" src="" alt="Logo Preview" class="logo-preview" style="display: none;">
            </div>
            @error('logo')
                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Business Name <span class="required">*</span></label>
            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                   name="company_name"
                   value="{{ old('company_name', $company->name !== 'Pending Setup' ? $company->name : '') }}"
                   required placeholder="Your Business Name">
            @error('company_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Business Phone</label>
            <input type="tel" class="form-control @error('company_phone') is-invalid @enderror"
                   name="company_phone"
                   value="{{ old('company_phone', $company->phone) }}"
                   placeholder="+255 123 456 789">
            @error('company_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Business Address</label>
            <textarea class="form-control @error('company_address') is-invalid @enderror"
                      name="company_address" rows="2"
                      placeholder="123 Main Street, City, Country">{{ old('company_address', $company->address) }}</textarea>
            @error('company_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">
            Continue
            <i class="bi bi-arrow-right"></i>
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('logoUploadArea');
            const logoInput = document.getElementById('logoInput');
            const logoPreview = document.getElementById('logoPreview');
            const logoPlaceholder = document.getElementById('logoPlaceholder');

            logoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        logoPreview.src = event.target.result;
                        logoPreview.style.display = 'block';
                        logoPlaceholder.style.display = 'none';
                        uploadArea.classList.add('has-preview');
                    };
                    reader.readAsDataURL(file);
                }
            });

            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.style.borderColor = '#007AFF';
                uploadArea.style.background = 'rgba(0, 122, 255, 0.04)';
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.style.borderColor = '#D2D2D7';
                uploadArea.style.background = '#F5F5F7';
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.style.borderColor = '#D2D2D7';
                uploadArea.style.background = '#F5F5F7';

                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    logoInput.files = e.dataTransfer.files;
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        logoPreview.src = event.target.result;
                        logoPreview.style.display = 'block';
                        logoPlaceholder.style.display = 'none';
                        uploadArea.classList.add('has-preview');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</x-onboarding-layout>
