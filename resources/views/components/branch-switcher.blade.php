@php
    $user = auth()->user();
    $company = $user->company;
    $currentBranch = $user->currentBranch();
    $accessibleBranches = $user->accessibleBranches()->get();
@endphp

@if($company && $company->hasBranchesEnabled() && $accessibleBranches->count() > 1)
    <div class="branch-switcher mb-3">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm w-100 d-flex justify-content-between align-items-center"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span>
                    <i class="bi bi-building me-1"></i>
                    {{ $currentBranch?->name ?? 'Select Branch' }}
                </span>
                <i class="bi bi-chevron-down"></i>
            </button>
            <ul class="dropdown-menu w-100">
                @foreach($accessibleBranches as $branch)
                    <li>
                        <form action="{{ route('branch.switch', $branch) }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item {{ $currentBranch?->id === $branch->id ? 'active' : '' }}">
                                <i class="bi bi-{{ $branch->is_main ? 'building-fill' : 'building' }} me-1"></i>
                                {{ $branch->name }}
                                @if($branch->code)
                                    <span class="badge bg-light text-dark ms-1">{{ $branch->code }}</span>
                                @endif
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <style>
        .branch-switcher .dropdown-menu {
            padding: 0.5rem;
        }
        .branch-switcher .dropdown-item {
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
        }
        .branch-switcher .dropdown-item.active {
            background: rgba(0, 122, 255, 0.1);
            color: var(--apple-blue);
        }
        .branch-switcher .dropdown-item:hover:not(.active) {
            background: #f5f5f7;
        }
    </style>
@endif
