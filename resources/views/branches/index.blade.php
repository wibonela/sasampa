<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Branches</h1>
                <p class="page-subtitle">Manage your business locations</p>
            </div>
            @if(auth()->user()->isCompanyOwner())
                <a href="{{ route('branches.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Add Branch
                </a>
            @endif
        </div>

        <!-- Branch Settings Info -->
        @if(auth()->user()->company->hasBranchesEnabled())
            <div class="alert alert-info d-flex align-items-center mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <div>
                    <strong>Branch Mode:</strong>
                    @if(auth()->user()->company->hasSharedProducts())
                        Shared - Products and categories are shared across all branches.
                    @else
                        Independent - Each branch manages its own products and categories.
                    @endif
                </div>
            </div>
        @endif

        <!-- Branches Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Code</th>
                            <th>Contact</th>
                            <th>Users</th>
                            <th>Status</th>
                            <th style="width: 120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-building" style="color: var(--apple-blue); font-size: 18px;"></i>
                                        </div>
                                        <div>
                                            <span style="font-weight: 500;">{{ $branch->name }}</span>
                                            @if($branch->is_main)
                                                <span class="badge bg-primary ms-1">Main</span>
                                            @endif
                                            @if($branch->address)
                                                <div class="text-secondary small">{{ Str::limit($branch->address, 40) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($branch->code)
                                        <span class="badge bg-light text-dark">{{ $branch->code }}</span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td class="text-secondary">
                                    @if($branch->phone || $branch->email)
                                        <div>{{ $branch->phone ?? '-' }}</div>
                                        <div class="small">{{ $branch->email ?? '' }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('branches.users', $branch) }}" class="text-decoration-none">
                                        <span class="badge bg-secondary">{{ $branch->users->count() }} users</span>
                                    </a>
                                </td>
                                <td>
                                    @if($branch->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if(auth()->user()->isCompanyOwner())
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('branches.users', $branch) }}" class="btn btn-sm btn-outline-secondary" title="Manage Users">
                                                <i class="bi bi-people"></i>
                                            </a>
                                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @unless($branch->is_main)
                                                <form action="{{ route('branches.destroy', $branch) }}" method="POST"
                                                      onsubmit="return confirm('Delete this branch? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    <i class="bi bi-building" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                                    <p class="mt-3 mb-0">No branches found</p>
                                    @if(auth()->user()->isCompanyOwner())
                                        <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm mt-2">Add Branch</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
