<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\MembershipRequest;
use App\Models\Pengembalian;
use App\Policies\BookPolicy;
use App\Policies\MembershipPolicy;
use App\Policies\ReturnPolicy;
use Illuminate\Support\Facades\Gate;
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
    }
}
