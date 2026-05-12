<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.custom' => \App\Http\Middleware\CustomAuth::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'librarian' => \App\Http\Middleware\LibrarianMiddleware::class,
            'member' => \App\Http\Middleware\MemberMiddleware::class,
            'role.redirect' => \App\Http\Middleware\RoleBasedRedirect::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, $request) {
            $status = $e->getStatusCode();

            if (in_array($status, [401, 403, 404], true)) {
                return response()->view("errors.{$status}", ['exception' => $e], $status);
            }

            return null;
        });
    })->create();
