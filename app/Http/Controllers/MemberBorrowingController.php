<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Pinjam;
use App\Models\RenewalRequest;
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
        $libraryCard = $anggota?->libraryCard;

        $activeBorrowings = $anggota
            ? Pinjam::with(['book', 'fine'])
                ->where('anggota_id', $anggota->id)
                ->where('status', 'dipinjam')
                ->latest()
                ->paginate(10)
            : collect();

        $pendingRenewals = $anggota
            ? RenewalRequest::where('anggota_id', $anggota->id)
                ->where('status', 'pending')
                ->pluck('notes', 'pinjam_id')
            : collect();

        $activeReservations = $anggota
            ? BookReservation::with('book')
                ->where('anggota_id', $anggota->id)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->latest()
                ->get()
            : collect();

        return view('member.borrowings', compact('activeBorrowings', 'libraryCard', 'pendingRenewals', 'activeReservations'));
    }

    public function store(Request $request, BorrowingService $borrowingService): RedirectResponse
    {
        $validated = $request->validate([
            'card_number' => ['required', 'string'],
            'book_barcode' => ['required', 'string'],
        ]);

        try {
            $book = Book::where('id_buku', $validated['book_barcode'])->firstOrFail();
            $anggota = $request->user()?->anggota;
            abort_unless($anggota, 403);

            if (($anggota->libraryCard?->card_number ?? null) !== $validated['card_number']) {
                return back()->with('error', 'Nomor kartu tidak sesuai dengan akun member yang sedang login.');
            }

            $borrowingService->reserve($anggota->id, $book->id, $request->user()->id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Buku berhasil direservasi selama 1 hari.');
    }

    public function renew(Request $request, Pinjam $pinjam): RedirectResponse
    {
        abort_if($pinjam->anggota?->user_id !== $request->user()?->id, 403);
        abort_if($pinjam->status !== 'dipinjam', 422, 'Peminjaman ini tidak aktif.');

        $exists = RenewalRequest::where('anggota_id', $pinjam->anggota_id)
            ->where('status', 'pending')
            ->where('pinjam_id', $pinjam->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Permintaan perpanjangan untuk buku ini sudah menunggu persetujuan pustakawan.');
        }

        RenewalRequest::create([
            'user_id' => $request->user()->id,
            'anggota_id' => $pinjam->anggota_id,
            'pinjam_id' => $pinjam->id,
            'status' => 'pending',
            'notes' => 'Permintaan perpanjangan untuk buku: ' . ($pinjam->book?->judul ?? '-'),
        ]);

        return back()->with('success', 'Permintaan perpanjangan berhasil dikirim ke librarian untuk disetujui.');
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
            $borrowingService->reserve($anggota->id, $book->id, $request->user()->id);
        } catch (\Throwable $e) {
            return back()->with('error', 'Reservasi gagal: ' . $e->getMessage());
        }

        return back()->with('success', 'Buku berhasil direservasi selama 1 hari.');
    }
}
