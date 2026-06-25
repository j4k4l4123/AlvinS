<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\MembershipRequest;
use App\Models\Pengembalian;
use App\Models\SystemNotification;
use App\Policies\BookPolicy;
use App\Policies\MembershipPolicy;
use App\Policies\ReturnPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Book::class, BookPolicy::class);
        Gate::policy(MembershipRequest::class, MembershipPolicy::class);
        Gate::policy(Pengembalian::class, ReturnPolicy::class);

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            $navbarNotifications = collect();

            if ($user) {
                $navbarNotifications = \Illuminate\Support\Facades\DB::table('notifications')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                foreach ($navbarNotifications as $item) {
                    if (isset($item->created_at)) {
                        $item->created_at = \Carbon\Carbon::parse($item->created_at);
                    }
                }
            }

            $view->with('navbarNotifications', $navbarNotifications);
        });
    }
}
