<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = SystemNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('member.notifications', compact('notifications'));
    }

    public function markAsRead(SystemNotification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);

        $notification->update([
            'read_at' => now(),
        ]);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
}
