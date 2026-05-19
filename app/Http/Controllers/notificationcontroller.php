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
}
