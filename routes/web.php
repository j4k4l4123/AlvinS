<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

Route::get('/', [PageController::class, 'login']);
Route::post('/login', [PageController::class, 'handleLogin']);
Route::get('/dashboard', [PageController::class, 'dashboard']);
Route::get('/test', [PageController::class, 'test']);



