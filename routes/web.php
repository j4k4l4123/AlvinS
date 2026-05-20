<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginPostController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\LibraryCardController;
use App\Http\Controllers\LibrarianRegistrationRequestController;
use App\Http\Controllers\MemberBorrowingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RenewalRequestController;
use App\Http\Controllers\ReservationApprovalController;
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
use App\Models\LibrarianRegistrationRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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
    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
});

Route::prefix('librarian')->middleware(['auth', LibrarianMiddleware::class])->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('librarian.dashboard');
    Route::get('/books', [BookController::class, 'books'])->name('books.index');
    Route::get('/racks', [RackController::class, 'index'])->name('racks.index');
    Route::get('/racks/create', [RackController::class, 'create'])->name('racks.create');
    Route::post('/racks', [RackController::class, 'store'])->name('racks.store');
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
    Route::put('/pinjam/{id}/lost', [PinjamController::class, 'markLost'])->name('pinjam.lost');
    Route::put('/pinjam/{id}/damaged', [PinjamController::class, 'markDamaged'])->name('pinjam.damaged');
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
    Route::get('/membership-requests/reservasi', [MembershipRequestController::class, 'reservations'])->name('membership-requests.reservations');
    Route::get('/membership-requests/reservasi/{reservation}', [MembershipRequestController::class, 'reservationsShow'])->name('membership-requests.reservations.show');
    Route::get('/membership-requests/pembatalan', [MembershipRequestController::class, 'cancellations'])->name('membership-requests.cancellations');
    Route::get('/membership-requests/perpanjangan', [MembershipRequestController::class, 'renewals'])->name('membership-requests.renewals');
    Route::get('/membership-requests/perpanjangan/{renewalRequest}', [MembershipRequestController::class, 'renewalsShow'])->name('membership-requests.renewals.show');
    Route::get('/membership-requests/pengajuan-librarian', [MembershipRequestController::class, 'librarianRegistrations'])->name('membership-requests.librarian-registrations');
    Route::get('/membership-requests/pengajuan-librarian/{librarianRegistrationRequest}', [MembershipRequestController::class, 'librarianRegistrationsShow'])->name('membership-requests.librarian-registrations.show');
    Route::get('/librarian-registration-requests', [LibrarianRegistrationRequestController::class, 'index'])->name('librarian-registration-requests.index');
    Route::get('/membership-requests/{id}', [MembershipRequestController::class, 'show'])->name('membership-requests.show');
    Route::put('/membership-requests/{id}', [MembershipRequestController::class, 'update'])->name('membership-requests.update');
    Route::put('/librarian-registration-requests/{librarianRegistrationRequest}', [LibrarianRegistrationRequestController::class, 'update'])->name('librarian-registration-requests.update');
    Route::put('/renewal-requests/{renewalRequest}', [RenewalRequestController::class, 'update'])->name('renewal-requests.update');
    Route::put('/reservations/{reservation}', [ReservationApprovalController::class, 'update'])->name('reservations.update');
});

Route::prefix('member')->middleware(['auth', MemberMiddleware::class])->group(function () {
    Route::get('/dashboard', [MemberController::class, 'dashboard'])->name('member.dashboard');
    Route::get('/books', [BookController::class, 'books'])->name('member.books.index');
    Route::get('/books/{id}', [BookController::class, 'show'])->name('member.books.show');
    Route::post('/books/{book}/reserve', [MemberBorrowingController::class, 'reserve'])->name('member.books.reserve');
    Route::get('/borrowings', [MemberBorrowingController::class, 'index'])->name('member.borrowings.index');
    Route::post('/borrowings', [MemberBorrowingController::class, 'store'])->name('member.borrowings.store');
    Route::delete('/reservations/{reservation}', [MemberBorrowingController::class, 'cancelReservation'])->name('member.reservations.cancel');
    Route::put('/borrowings/{pinjam}/renew', [MemberBorrowingController::class, 'renew'])->name('member.borrowings.renew');
    Route::get('/history', [MemberController::class, 'dashboard'])->name('member.history');
    Route::get('/library-card', [LibraryCardController::class, 'show'])->name('member.library-card');
    Route::get('/profile/edit', [MemberProfileController::class, 'edit'])->name('member.profile.edit');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
    Route::get('/pengajuan', function () {
        $librarianRequestFeatureReady = Schema::hasTable('librarian_registration_requests');
        $hasPendingLibrarianRequest = $librarianRequestFeatureReady
            ? LibrarianRegistrationRequest::where('user_id', auth()->id())
                ->where('status', 'pending')
                ->exists()
            : false;

        return view('member.submissions', compact('librarianRequestFeatureReady', 'hasPendingLibrarianRequest'));
    })->name('member.submissions');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('member.notifications');
    Route::get('/fines', [FineController::class, 'index'])->name('member.fines');
    Route::put('/fines/{fine}/pay', [FineController::class, 'pay'])->name('member.fines.pay');
    Route::view('/cancel-membership', 'membership.cancel')->name('member.cancel-membership');
    Route::post('/membership-requests', [MembershipRequestController::class, 'store'])->name('membership-requests.store');
    Route::delete('/membership-requests/pending', [MembershipRequestController::class, 'cancelOwnPending'])->name('membership-requests.cancel-own');
    Route::post('/librarian-registration-requests', [LibrarianRegistrationRequestController::class, 'store'])->name('librarian-registration-requests.store');
});

Route::get('/test', [PageController::class, 'test']);
