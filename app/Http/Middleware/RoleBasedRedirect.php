<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        // This middleware is intended for routes like "login success" or default landing.
        // If the user has no role assigned, treat as unauthorized.
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $role = $user->roles()->first();
        if (!$role) {
            abort(403);
        }

        // If the user is trying to access the dashboard route, redirect them to role dashboard.
        if ($request->route()?->getName() === 'dashboard') {
            return $role->name === 'librarian'
                ? redirect()->route('librarian.dashboard')
                : redirect()->route('member.dashboard');
        }

        return $next($request);
    }

}
