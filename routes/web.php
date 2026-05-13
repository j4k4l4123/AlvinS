<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginPostController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\LibraryCardController;
use App\Http\Controllers\MemberBorrowingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RenewalRequestController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\MemberProfileController;
use App\Http\Controllers\MembershipRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\PinjamController;
use App\Http\Middleware\LibrarianMiddleware;
use App\Http\Middleware\MemberMiddleware;
use App\Http\Middleware\RoleBasedRedirect;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {
    Route::get('/', [PageController::class, 'login']);
    Route::get('/login', [PageController::class, 'login'])->name('login');
    Route::post('/login', LoginPostController::class)->name('login.post');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.post');

    Route::get('/password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [PageController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [PageController::class, 'dashboard'])
        ->name('dashboard')
        ->middleware(RoleBasedRedirect::class);
});

Route::prefix('librarian')->middleware(['auth', LibrarianMiddleware::class])->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('librarian.dashboard');

    Route::get('/database', [PageController::class, 'database'])->name('pengguna.index');
    Route::get('/pengguna/create', [PageController::class, 'create'])->name('pengguna.create');
    Route::post('/pengguna', [PageController::class, 'store'])->name('pengguna.store');
    Route::get('/pengguna/{id}/edit', [PageController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{id}', [PageController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [PageController::class, 'destroy'])->name('pengguna.destroy');

    Route::get('/books', [BookController::class, 'books'])->name('books.index');
    Route::get('/racks', [RackController::class, 'index'])->name('racks.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');
    Route::get('/books/{id}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{id}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('books.destroy');

    Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota.index');
    Route::redirect('/anggota/create', '/register')->name('anggota.create');
    Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store');
    Route::get('/anggota/{id}', [AnggotaController::class, 'show'])->name('anggota.show');
    Route::get('/anggota/{id}/edit', [AnggotaController::class, 'edit'])->name('anggota.edit');
    Route::put('/anggota/{id}', [AnggotaController::class, 'update'])->name('anggota.update');
    Route::delete('/anggota/{id}', [AnggotaController::class, 'destroy'])->name('anggota.destroy');

    Route::get('/pinjam', [PinjamController::class, 'index'])->name('pinjam.index');
    Route::get('/pinjam/create', [PinjamController::class, 'create'])->name('pinjam.create');
    Route::post('/pinjam', [PinjamController::class, 'store'])->name('pinjam.store');
    Route::get('/pinjam/overdue', [PinjamController::class, 'overdue'])->name('pinjam.overdue');
    Route::get('/pinjam/{id}', [PinjamController::class, 'show'])->name('pinjam.show');
    Route::get('/pinjam/{id}/edit', [PinjamController::class, 'edit'])->name('pinjam.edit');
    Route::put('/pinjam/{id}', [PinjamController::class, 'update'])->name('pinjam.update');
    Route::delete('/pinjam/{id}', [PinjamController::class, 'destroy'])->name('pinjam.destroy');

    Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
    Route::get('/pengembalian/create', [PengembalianController::class, 'create'])->name('pengembalian.create');
    Route::post('/pengembalian', [PengembalianController::class, 'store'])->name('pengembalian.store');
    Route::get('/pengembalian/{id}', [PengembalianController::class, 'show'])->name('pengembalian.show');
    Route::delete('/pengembalian/{id}', [PengembalianController::class, 'destroy'])->name('pengembalian.destroy');
    Route::get('/pengembalian/fines/{anggotaId}', [PengembalianController::class, 'fines'])->name('pengembalian.fines');

    Route::get('/library-cards', [LibraryCardController::class, 'index'])->name('library-cards.index');
    Route::get('/library-cards/create', [LibraryCardController::class, 'create'])->name('library-cards.create');
    Route::post('/library-cards', [LibraryCardController::class, 'store'])->name('library-cards.store');
    Route::get('/library-cards/{id}', [LibraryCardController::class, 'show'])->name('library-cards.show');
    Route::put('/library-cards/{id}/toggle', [LibraryCardController::class, 'toggleStatus'])->name('library-cards.toggle');

    Route::get('/membership-requests', [MembershipRequestController::class, 'index'])->name('membership-requests.index');
    Route::get('/membership-requests/{id}', [MembershipRequestController::class, 'show'])->name('membership-requests.show');
    Route::put('/membership-requests/{id}', [MembershipRequestController::class, 'update'])->name('membership-requests.update');
    Route::get('/renewal-requests', [RenewalRequestController::class, 'index'])->name('renewal-requests.index');
    Route::get('/renewal-requests/{renewalRequest}', [RenewalRequestController::class, 'show'])->name('renewal-requests.show');
    Route::put('/renewal-requests/{renewalRequest}', [RenewalRequestController::class, 'update'])->name('renewal-requests.update');
});

Route::prefix('member')->middleware(['auth', MemberMiddleware::class])->group(function () {
    Route::get('/dashboard', [MemberController::class, 'dashboard'])->name('member.dashboard');
    Route::get('/books', [BookController::class, 'books'])->name('member.books.index');
    Route::post('/books/{book}/reserve', [MemberBorrowingController::class, 'reserve'])->name('member.books.reserve');
    Route::get('/borrowings', [MemberBorrowingController::class, 'index'])->name('member.borrowings.index');
    Route::post('/borrowings', [MemberBorrowingController::class, 'store'])->name('member.borrowings.store');
    Route::put('/borrowings/{pinjam}/renew', [MemberBorrowingController::class, 'renew'])->name('member.borrowings.renew');
    Route::put('/borrowings/{pinjam}/return', [MemberBorrowingController::class, 'returnBook'])->name('member.borrowings.return');
    Route::get('/history', [MemberController::class, 'dashboard'])->name('member.history');
    Route::get('/library-card', [LibraryCardController::class, 'show'])->name('member.library-card');
    Route::get('/profile/edit', [MemberProfileController::class, 'edit'])->name('member.profile.edit');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('member.notifications');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('member.notifications.read');
    Route::get('/fines', [FineController::class, 'index'])->name('member.fines');
    Route::put('/fines/{fine}/pay', [FineController::class, 'pay'])->name('member.fines.pay');
    Route::view('/cancel-membership', 'membership.cancel')->name('member.cancel-membership');
    Route::post('/membership-requests', [MembershipRequestController::class, 'store'])->name('membership-requests.store');
    Route::delete('/membership-requests/pending', [MembershipRequestController::class, 'cancelOwnPending'])->name('membership-requests.cancel-own');
});

Route::get('/test', [PageController::class, 'test']);
