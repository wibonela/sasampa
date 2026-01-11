<x-app-layout>
    <div class="fade-in">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="page-title">Notifications</h1>
                <p class="page-subtitle">
                    @if($unreadCount > 0)
                        {{ $unreadCount }} unread {{ Str::plural('notification', $unreadCount) }}
                    @else
                        All caught up
                    @endif
                </p>
            </div>
            @if($unreadCount > 0)
                <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-check-all me-1"></i>Mark All Read
                    </button>
                </form>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="card">
            @forelse($notifications as $notification)
                <div class="d-flex align-items-start px-4 py-3 border-bottom {{ $notification->isUnread() ? '' : '' }}" style="border-color: var(--apple-border) !important; {{ $notification->isUnread() ? 'background: var(--apple-gray-6);' : '' }}">
                    <div class="me-3" style="width: 36px; height: 36px; border-radius: 10px; background: rgba(0, 122, 255, 0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bi {{ $notification->icon }}" style="color: var(--apple-{{ $notification->color === 'primary' ? 'blue' : ($notification->color === 'success' ? 'green' : ($notification->color === 'warning' ? 'orange' : 'red')) }}); font-size: 16px;"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div style="font-size: 14px; font-weight: {{ $notification->isUnread() ? '600' : '400' }}; color: var(--apple-text);">
                                    {{ $notification->title }}
                                    @if($notification->isUnread())
                                        <span class="badge bg-primary ms-2">New</span>
                                    @endif
                                </div>
                                <div class="text-secondary" style="font-size: 13px; margin-top: 2px;">{{ $notification->message }}</div>
                                <div class="text-secondary" style="font-size: 11px; margin-top: 4px;">
                                    <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="d-flex gap-2 ms-3" style="flex-shrink: 0;">
                                @if($notification->action_url)
                                    <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary">View</a>
                                @endif
                                @if($notification->isUnread())
                                    <form action="{{ route('admin.notifications.read', $notification) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as read">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash" style="font-size: 48px; color: var(--apple-gray-3);"></i>
                    <p class="text-secondary mt-3 mb-0">No notifications yet</p>
                </div>
            @endforelse

            @if($notifications->hasPages())
                <div class="card-footer">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
