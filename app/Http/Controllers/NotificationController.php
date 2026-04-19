<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Full paginated notification list — Notifications/Index.vue.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     * Called by NotificationBell.vue when user clicks a notification.
     */
    public function markRead(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification && ! $notification->read_at) {
            $notification->markAsRead();
        }

        return back();
    }

    /**
     * Mark all unread notifications as read.
     * Called by the "Mark all read" button in NotificationBell and Index.
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}