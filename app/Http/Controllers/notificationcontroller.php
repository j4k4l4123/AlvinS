<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = DB::table('notifications')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $notifications->getCollection()->transform(function ($item) {
            if (isset($item->created_at)) {
                $item->created_at = \Carbon\Carbon::parse($item->created_at);
            }
            return $item;
        });

        return view('member.notifications', compact('notifications'));
    }
}
