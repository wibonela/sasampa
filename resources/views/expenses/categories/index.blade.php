<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Expense Categories</h1>
                <p class="page-subtitle">Organize your expenses by category</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Expenses
                </a>
                <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Add Category
                </a>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Expenses</th>
                            <th>Status</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255, 149, 0, 0.1); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-folder" style="color: var(--apple-orange);"></i>
                                        </div>
                                        <span style="font-weight: 500;">{{ $category->name }}</span>
                                    </div>
                                </td>
                                <td class="text-secondary">
                                    {{ Str::limit($category->description, 50) ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $category->expenses_count }}</span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('expense-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('expense-categories.destroy', $category) }}" method="POST"
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
                                    <i class="bi bi-folder" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No expense categories found</p>
                                    <a href="{{ route('expense-categories.create') }}" class="btn btn-primary btn-sm mt-2">Add Category</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
                <div class="card-footer">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
