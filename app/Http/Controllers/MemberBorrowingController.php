<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Pinjam;
use App\Services\BorrowingService;
use App\Services\FineService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberBorrowingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $anggota = $user?->anggota;

        $activeBorrowings = $anggota
            ? Pinjam::with(['book', 'fine'])
                ->where('anggota_id', $anggota->id)
                ->where('status', 'dipinjam')
                ->latest()
                ->paginate(10)
            : collect();

        return view('member.borrowings', compact('activeBorrowings'));
    }

    public function store(Request $request, BorrowingService $borrowingService): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
        ]);

        $anggota = $request->user()?->anggota;

        abort_unless($anggota, 403);

        try {
            $borrowingService->borrow($anggota->id, (int) $validated['book_id']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Buku berhasil dipinjam.');
    }

    public function renew(Request $request, Pinjam $pinjam): RedirectResponse
    {
        abort_if($pinjam->anggota?->user_id !== $request->user()?->id, 403);
        abort_if($pinjam->status !== 'dipinjam', 422, 'Peminjaman ini tidak aktif.');

        $pinjam->update([
            'tanggal_kembali' => Carbon::parse($pinjam->tanggal_kembali)->addDays(BorrowingService::DEFAULT_BORROW_DAYS),
        ]);

        return back()->with('success', 'Peminjaman berhasil diperpanjang.');
    }

    public function returnBook(Request $request, Pinjam $pinjam, FineService $fineService): RedirectResponse
    {
        abort_if($pinjam->anggota?->user_id !== $request->user()?->id, 403);
        abort_if($pinjam->status !== 'dipinjam', 422, 'Buku ini sudah dikembalikan.');

        $fineService->processReturn($pinjam->id, now()->toDateString());

        return back()->with('success', 'Buku berhasil dikembalikan.');
    }

    public function reserve(Request $request, Book $book, BorrowingService $borrowingService): RedirectResponse
    {
        $anggota = $request->user()?->anggota;
        abort_unless($anggota, 403);

        try {
            $borrowingService->borrow($anggota->id, $book->id);
        } catch (\Throwable $e) {
            return back()->with('error', 'Reservasi/peminjaman gagal: ' . $e->getMessage());
        }

        return back()->with('success', 'Buku berhasil direservasi/dipinjam.');
    }
}
