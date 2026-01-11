<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function __construct(
        private AdminNotificationService $notificationService
    ) {}

    public function index()
    {
        $notifications = AdminNotification::latest()->paginate(20);
        $unreadCount = $this->notificationService->getUnreadCount();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function dropdown()
    {
        $notifications = $this->notificationService->getRecent(5);
        $unreadCount = $this->notificationService->getUnreadCount();

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'action_url' => $notification->action_url,
                    'is_unread' => $notification->isUnread(),
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(AdminNotification $notification)
    {
        $this->notificationService->markAsRead($notification);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read');
    }
}
