<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

// Guest routes (only for NOT logged in users)
Route::middleware(['guest'])->group(function () {
    Route::get('/', [PageController::class, 'login']);
    Route::get('/login', [PageController::class, 'login'])->name('login');
    Route::post('/login', [PageController::class, 'handleLogin']);
});

// Protected routes (only for logged in users)
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard']);
    Route::post('/logout', [PageController::class, 'logout'])->name('logout');
});

// Test page (public for everyone)
Route::get('/test', [PageController::class, 'test']);

// Database page route (shows database data) - Using PageController
Route::get('/database', [PageController::class, 'database']);
