<x-app-layout>
    <x-slot name="header">Add Documentation Category</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.documentation.categories.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       id="slug" name="slug" value="{{ old('slug') }}" required
                                       placeholder="e.g., getting-started">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="icon" class="form-label">Icon</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                       id="icon" name="icon" value="{{ old('icon', 'bi-folder') }}"
                                       placeholder="bi-folder">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">English Translation</h5>

                        <div class="mb-3">
                            <label for="translations_en_name" class="form-label">Name (EN) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('translations.en.name') is-invalid @enderror"
                                   id="translations_en_name" name="translations[en][name]" value="{{ old('translations.en.name') }}" required>
                            @error('translations.en.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="translations_en_description" class="form-label">Description (EN)</label>
                            <textarea class="form-control @error('translations.en.description') is-invalid @enderror"
                                      id="translations_en_description" name="translations[en][description]" rows="2">{{ old('translations.en.description') }}</textarea>
                            @error('translations.en.description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Kiswahili Translation</h5>

                        <div class="mb-3">
                            <label for="translations_sw_name" class="form-label">Name (SW) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('translations.sw.name') is-invalid @enderror"
                                   id="translations_sw_name" name="translations[sw][name]" value="{{ old('translations.sw.name') }}" required>
                            @error('translations.sw.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="translations_sw_description" class="form-label">Description (SW)</label>
                            <textarea class="form-control @error('translations.sw.description') is-invalid @enderror"
                                      id="translations_sw_description" name="translations[sw][description]" rows="2">{{ old('translations.sw.description') }}</textarea>
                            @error('translations.sw.description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Create Category
                            </button>
                            <a href="{{ route('admin.documentation.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
