<x-app-layout>
    <div class="fade-in">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Documentation Categories</h1>
                <p class="page-subtitle">Manage documentation categories</p>
            </div>
            <a href="{{ route('admin.documentation.categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Add Category
            </a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Articles</th>
                            <th>Status</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                            <i class="{{ $category->icon }}" style="color: var(--apple-blue);"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 500;">{{ $category->getName('en') }}</div>
                                            <div class="text-secondary small">{{ $category->getName('sw') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>
                                    <span class="badge bg-secondary">{{ $category->articles_count }}</span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.documentation.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.documentation.categories.destroy', $category) }}" method="POST"
                                              onsubmit="return confirm('Delete this category?')">
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
                                    <i class="bi bi-folder" style="font-size: 48px;"></i>
                                    <p class="mt-3 mb-0">No categories found</p>
                                    <a href="{{ route('admin.documentation.categories.create') }}" class="btn btn-primary btn-sm mt-2">
                                        Add Category
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
