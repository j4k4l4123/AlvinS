<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in via session
        if (!session('is_logged_in')) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        return $next($request);
    }
}
