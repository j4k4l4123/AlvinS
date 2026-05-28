<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Pinjam;
use App\Models\RenewalRequest;
use App\Support\NotificationHelper;
use App\Services\BorrowingService;
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
                ->whereIn('status', ['pending', 'approved'])
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
            $pinjam = $borrowingService->borrowByBarcodes($validated['card_number'], $validated['book_barcode']);
            $pinjam->load('book.rack');

            $rackMessage = $pinjam->book?->rack?->name
                ? ' Rak buku: ' . $pinjam->book->rack->name . '.'
                : '';

            NotificationHelper::send(
                $request->user()->id,
                'borrowing_created',
                'Peminjaman berhasil dibuat',
                'Buku "' . ($pinjam->book?->judul ?? '-') . '" berhasil dipinjam.' . $rackMessage,
                ['pinjam_id' => $pinjam->id]
            );

            return back()->with('success', 'Buku berhasil dipinjam.' . $rackMessage);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
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

        $renewal = RenewalRequest::create([
            'user_id' => $request->user()->id,
            'anggota_id' => $pinjam->anggota_id,
            'pinjam_id' => $pinjam->id,
            'status' => 'pending',
            'notes' => 'Permintaan perpanjangan untuk buku: ' . ($pinjam->book?->judul ?? '-'),
        ]);

        NotificationHelper::send(
            $request->user()->id,
            'renewal_request_created',
            'Permintaan perpanjangan dikirim',
            'Permintaan perpanjangan untuk buku "' . ($pinjam->book?->judul ?? '-') . '" berhasil dikirim ke librarian.',
            ['renewal_request_id' => $renewal->id]
        );

        return back()->with('success', 'Permintaan perpanjangan berhasil dikirim ke librarian untuk disetujui.');
    }

    public function reserve(Request $request, Book $book, BorrowingService $borrowingService): RedirectResponse
    {
        $anggota = $request->user()?->anggota;
        abort_unless($anggota, 403);

        try {
            $reservation = $borrowingService->reserve($anggota->id, $book->id, $request->user()->id);

            NotificationHelper::send(
                $request->user()->id,
                'reservation_created',
                'Reservasi berhasil dibuat',
                'Reservasi buku "' . ($book->judul ?? '-') . '" berhasil diajukan dan sedang menunggu persetujuan librarian.',
                ['reservation_id' => $reservation->id]
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Reservasi gagal: ' . $e->getMessage());
        }

        return back()->with('success', 'Reservasi berhasil diajukan dan sedang menunggu persetujuan librarian.');
    }

    public function cancelReservation(Request $request, BookReservation $reservation): RedirectResponse
    {
        abort_if($reservation->user_id !== $request->user()?->id, 403);
        abort_if(! in_array($reservation->status, ['pending', 'approved'], true), 422, 'Reservasi ini tidak bisa dibatalkan.');

        $title = $reservation->book?->judul ?? '-';
        $reservation->delete();

        NotificationHelper::send(
            $request->user()->id,
            'reservation_cancelled',
            'Reservasi dibatalkan',
            'Reservasi buku "' . $title . '" berhasil dibatalkan.',
            []
        );

        return back()->with('success', 'Reservasi berhasil dibatalkan.');
    }
}
