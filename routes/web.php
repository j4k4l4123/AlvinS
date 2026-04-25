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
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
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
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/books', [BookController::class, 'books'])->name('books.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');
    Route::get('/books/{id}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{id}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('books.destroy');
});

// Anggota Routes
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/anggota', [App\Http\Controllers\AnggotaController::class, 'index'])->name('anggota.index');
    Route::get('/anggota/create', [App\Http\Controllers\AnggotaController::class, 'create'])->name('anggota.create');
    Route::post('/anggota', [App\Http\Controllers\AnggotaController::class, 'store'])->name('anggota.store');
    Route::get('/anggota/{id}', [App\Http\Controllers\AnggotaController::class, 'show'])->name('anggota.show');
    Route::get('/anggota/{id}/edit', [App\Http\Controllers\AnggotaController::class, 'edit'])->name('anggota.edit');
    Route::put('/anggota/{id}', [App\Http\Controllers\AnggotaController::class, 'update'])->name('anggota.update');
    Route::delete('/anggota/{id}', [App\Http\Controllers\AnggotaController::class, 'destroy'])->name('anggota.destroy');
});

// Pinjam Routes
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/pinjam', [App\Http\Controllers\PinjamController::class, 'index'])->name('pinjam.index');
    Route::get('/pinjam/create', [App\Http\Controllers\PinjamController::class, 'create'])->name('pinjam.create');
    Route::post('/pinjam', [App\Http\Controllers\PinjamController::class, 'store'])->name('pinjam.store');
    Route::get('/pinjam/{id}', [App\Http\Controllers\PinjamController::class, 'show'])->name('pinjam.show');
    Route::get('/pinjam/{id}/edit', [App\Http\Controllers\PinjamController::class, 'edit'])->name('pinjam.edit');
    Route::put('/pinjam/{id}', [App\Http\Controllers\PinjamController::class, 'update'])->name('pinjam.update');
    Route::delete('/pinjam/{id}', [App\Http\Controllers\PinjamController::class, 'destroy'])->name('pinjam.destroy');
});

// Pengembalian Routes
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/pengembalian', [App\Http\Controllers\PengembalianController::class, 'index'])->name('pengembalian.index');
    Route::get('/pengembalian/create', [App\Http\Controllers\PengembalianController::class, 'create'])->name('pengembalian.create');
    Route::post('/pengembalian', [App\Http\Controllers\PengembalianController::class, 'store'])->name('pengembalian.store');
    Route::get('/pengembalian/{id}', [App\Http\Controllers\PengembalianController::class, 'show'])->name('pengembalian.show');
    Route::delete('/pengembalian/{id}', [App\Http\Controllers\PengembalianController::class, 'destroy'])->name('pengembalian.destroy');
});
