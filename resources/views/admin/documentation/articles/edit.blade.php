<x-app-layout>
    <x-slot name="header">Edit Documentation Article</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.documentation.articles.update', $article) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $article->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->getName('en') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       id="slug" name="slug" value="{{ old('slug', $article->slug) }}" required>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="sort_order" class="form-label">Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $article->sort_order) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1"
                                           {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">Published</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1"
                                           {{ old('is_featured', $article->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">Featured on homepage</label>
                                </div>
                            </div>
                        </div>

                        @php
                            $enTranslation = $article->translations->where('locale', 'en')->first();
                            $swTranslation = $article->translations->where('locale', 'sw')->first();
                        @endphp

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-translate me-1"></i>English Content</h5>

                        <div class="mb-3">
                            <label for="translations_en_title" class="form-label">Title (EN) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('translations.en.title') is-invalid @enderror"
                                   id="translations_en_title" name="translations[en][title]"
                                   value="{{ old('translations.en.title', $enTranslation?->title) }}" required>
                            @error('translations.en.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="translations_en_excerpt" class="form-label">Excerpt (EN)</label>
                            <textarea class="form-control @error('translations.en.excerpt') is-invalid @enderror"
                                      id="translations_en_excerpt" name="translations[en][excerpt]" rows="2"
                                      maxlength="500">{{ old('translations.en.excerpt', $enTranslation?->excerpt) }}</textarea>
                            @error('translations.en.excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="translations_en_content" class="form-label">Content (EN) <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('translations.en.content') is-invalid @enderror"
                                      id="translations_en_content" name="translations[en][content]" rows="10" required>{{ old('translations.en.content', $enTranslation?->content) }}</textarea>
                            @error('translations.en.content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Markdown formatting is supported.</div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-translate me-1"></i>Kiswahili Content</h5>

                        <div class="mb-3">
                            <label for="translations_sw_title" class="form-label">Title (SW) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('translations.sw.title') is-invalid @enderror"
                                   id="translations_sw_title" name="translations[sw][title]"
                                   value="{{ old('translations.sw.title', $swTranslation?->title) }}" required>
                            @error('translations.sw.title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="translations_sw_excerpt" class="form-label">Excerpt (SW)</label>
                            <textarea class="form-control @error('translations.sw.excerpt') is-invalid @enderror"
                                      id="translations_sw_excerpt" name="translations[sw][excerpt]" rows="2"
                                      maxlength="500">{{ old('translations.sw.excerpt', $swTranslation?->excerpt) }}</textarea>
                            @error('translations.sw.excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="translations_sw_content" class="form-label">Content (SW) <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('translations.sw.content') is-invalid @enderror"
                                      id="translations_sw_content" name="translations[sw][content]" rows="10" required>{{ old('translations.sw.content', $swTranslation?->content) }}</textarea>
                            @error('translations.sw.content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Article
                            </button>
                            <a href="{{ route('admin.documentation.articles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
