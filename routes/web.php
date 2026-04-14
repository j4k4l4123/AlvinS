<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BookController;

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
    
    // CRUD Routes for Pengguna
    Route::get('/database', [PageController::class, 'database'])->name('pengguna.index');
    Route::get('/pengguna/create', [PageController::class, 'create'])->name('pengguna.create');
    Route::post('/pengguna', [PageController::class, 'store'])->name('pengguna.store');
    Route::get('/pengguna/{id}/edit', [PageController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{id}', [PageController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [PageController::class, 'destroy'])->name('pengguna.destroy');
});

// Test page (public for everyone)
Route::get('/test', [PageController::class, 'test']);

// Book System Routes
Route::get('/books', [BookController::class, 'books'])->name('books.index');
Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
Route::post('/books', [BookController::class, 'store'])->name('books.store');
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');
Route::get('/books/{id}/edit', [BookController::class, 'edit'])->name('books.edit');
Route::put('/books/{id}', [BookController::class, 'update'])->name('books.update');
Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('books.destroy');