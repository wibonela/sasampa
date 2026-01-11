<x-app-layout>
    <div class="fade-in">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Documentation Articles</h1>
                <p class="page-subtitle">Manage documentation articles</p>
            </div>
            <a href="{{ route('admin.documentation.articles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Add Article
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->getName('en') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    @if(request('category') || request('status'))
                        <div class="col-md-4">
                            <a href="{{ route('admin.documentation.articles.index') }}" class="btn btn-outline-secondary">
                                Clear Filters
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th style="width: 120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                            <tr>
                                <td>
                                    <div>
                                        <div style="font-weight: 500;">{{ $article->getTitle('en') }}</div>
                                        <div class="text-secondary small">{{ $article->getTitle('sw') }}</div>
                                        <code class="small">{{ $article->slug }}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="{{ $article->category->icon }} me-1"></i>
                                        {{ $article->category->getName('en') }}
                                    </span>
                                </td>
                                <td>
                                    @if($article->is_published)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    @if($article->is_featured)
                                        <span class="badge bg-info">Featured</span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form action="{{ route('admin.documentation.articles.toggle-publish', $article) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $article->is_published ? 'warning' : 'success' }}"
                                                    title="{{ $article->is_published ? 'Unpublish' : 'Publish' }}">
                                                <i class="bi bi-{{ $article->is_published ? 'eye-slash' : 'eye' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.documentation.articles.edit', $article) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.documentation.articles.destroy', $article) }}" method="POST"
                                              onsubmit="return confirm('Delete this article?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-secondary">
                                    <i class="bi bi-file-text" style="font-size: 48px;"></i>
                                    <p class="mt-3 mb-0">No articles found</p>
                                    <a href="{{ route('admin.documentation.articles.create') }}" class="btn btn-primary btn-sm mt-2">
                                        Add Article
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($articles->hasPages())
                <div class="card-footer">
                    {{ $articles->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
