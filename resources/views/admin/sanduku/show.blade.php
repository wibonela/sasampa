<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <a href="{{ route('admin.sanduku.index') }}" class="text-secondary text-decoration-none mb-2 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i> Back to Feedback
                </a>
                <h1 class="page-title">{{ $sanduku->title }}</h1>
                <p class="page-subtitle">
                    @if($sanduku->type === 'bug')
                        <span class="badge bg-danger me-2">Bug Report</span>
                    @else
                        <span class="badge bg-info me-2">Feedback</span>
                    @endif
                    Submitted {{ $sanduku->created_at->diffForHumans() }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.sanduku.destroy', $sanduku) }}" method="POST"
                      data-confirm='{"title":"Delete Feedback","message":"Are you sure you want to delete this feedback?","type":"danger","confirmText":"Delete"}'>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Description</h5>
                    </div>
                    <div class="card-body">
                        @if($sanduku->description)
                            <p style="white-space: pre-wrap;">{{ $sanduku->description }}</p>
                        @else
                            <p class="text-secondary mb-0">No description provided.</p>
                        @endif
                    </div>
                </div>

                @if($sanduku->screenshot)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Screenshot</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ Storage::url($sanduku->screenshot) }}" target="_blank">
                                <img src="{{ Storage::url($sanduku->screenshot) }}"
                                     alt="Screenshot"
                                     class="img-fluid rounded border"
                                     style="max-height: 500px;">
                            </a>
                            <div class="mt-2">
                                <a href="{{ Storage::url($sanduku->screenshot) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrows-fullscreen me-1"></i> View Full Size
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                @if($sanduku->user_agent)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Technical Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong class="text-secondary">User Agent:</strong>
                            </div>
                            <code class="d-block p-2 bg-light rounded" style="font-size: 12px; word-break: break-all;">
                                {{ $sanduku->user_agent }}
                            </code>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.sanduku.update-status', $sanduku) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select mb-3" onchange="this.form.submit()">
                                <option value="new" {{ $sanduku->status === 'new' ? 'selected' : '' }}>New</option>
                                <option value="reviewed" {{ $sanduku->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                <option value="resolved" {{ $sanduku->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </form>
                        <div class="text-secondary" style="font-size: 12px;">
                            Select a status to update
                        </div>
                    </div>
                </div>

                <!-- Details Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-secondary" style="width: 100px;">Type</td>
                                <td>
                                    @if($sanduku->type === 'bug')
                                        <span class="badge bg-danger">Bug</span>
                                    @else
                                        <span class="badge bg-info">Feedback</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Contact</td>
                                <td>
                                    @if($sanduku->contact)
                                        <a href="mailto:{{ $sanduku->contact }}">{{ $sanduku->contact }}</a>
                                    @else
                                        <span class="text-secondary">Anonymous</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Page URL</td>
                                <td>
                                    @if($sanduku->page_url)
                                        <a href="{{ $sanduku->page_url }}" target="_blank" title="{{ $sanduku->page_url }}">
                                            {{ Str::limit($sanduku->page_url, 25) }}
                                        </a>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Submitted</td>
                                <td>{{ $sanduku->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">ID</td>
                                <td>#{{ $sanduku->id }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
