<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Sanduku Feedback</h1>
                <p class="page-subtitle">User feedback and bug reports</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div class="stat-value">{{ $stats['new'] }}</div>
                    <div class="stat-label">New</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="stat-value">{{ $stats['reviewed'] }}</div>
                    <div class="stat-label">Reviewed</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['resolved'] }}</div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="bi bi-bug"></i>
                    </div>
                    <div class="stat-value">{{ $stats['bugs'] }}</div>
                    <div class="stat-label">Bugs</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon teal">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="stat-value">{{ $stats['feedback'] }}</div>
                    <div class="stat-label">Feedback</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" class="d-flex align-items-center gap-3">
                    <span class="text-secondary" style="font-size: 13px;">Filter</span>
                    <select name="type" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="feedback" {{ request('type') == 'feedback' ? 'selected' : '' }}>Feedback</option>
                        <option value="bug" {{ request('type') == 'bug' ? 'selected' : '' }}>Bug Report</option>
                    </select>
                    <select name="status" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                    @if(request('type') || request('status'))
                        <a href="{{ route('admin.sanduku.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Contact</th>
                            <th>Page</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feedbacks as $feedback)
                            <tr>
                                <td>
                                    @if($feedback->type === 'bug')
                                        <span class="badge bg-danger">Bug</span>
                                    @else
                                        <span class="badge bg-info">Feedback</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ Str::limit($feedback->title, 40) }}</div>
                                    @if($feedback->description)
                                        <div class="text-secondary" style="font-size: 12px;">{{ Str::limit($feedback->description, 50) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($feedback->contact)
                                        <span style="font-size: 13px;">{{ $feedback->contact }}</span>
                                    @else
                                        <span class="text-secondary">Anonymous</span>
                                    @endif
                                </td>
                                <td>
                                    @if($feedback->page_url)
                                        <span class="text-secondary" style="font-size: 12px;" title="{{ $feedback->page_url }}">
                                            {{ Str::limit(parse_url($feedback->page_url, PHP_URL_PATH) ?: '/', 20) }}
                                        </span>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 13px;">{{ $feedback->created_at->format('M d, Y') }}</div>
                                    <div class="text-secondary" style="font-size: 11px;">{{ $feedback->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    @switch($feedback->status)
                                        @case('new')
                                            <span class="badge bg-warning">New</span>
                                            @break
                                        @case('reviewed')
                                            <span class="badge bg-primary">Reviewed</span>
                                            @break
                                        @case('resolved')
                                            <span class="badge bg-success">Resolved</span>
                                            @break
                                        @default
                                            <span class="badge bg-warning">New</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.sanduku.show', $feedback) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <div class="mt-2">No feedback received yet</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($feedbacks->hasPages())
                <div class="card-footer">
                    {{ $feedbacks->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
