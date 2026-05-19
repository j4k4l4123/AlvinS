<?php

namespace App\Http\Controllers;

use App\Models\BookReservation;
use App\Support\NotificationHelper;
use App\Services\BorrowingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationApprovalController extends Controller
{
    public function index()
    {
        BookReservation::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        $reservations = BookReservation::with(['book.rack', 'anggota', 'user'])
            ->latest()
            ->paginate(12);

        return view('reservations.index', compact('reservations'));
    }

    public function update(Request $request, BookReservation $reservation, BorrowingService $borrowingService)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Reservasi ini sudah diproses atau tidak aktif.');
        }

        if ($reservation->expires_at && $reservation->expires_at->isPast()) {
            $reservation->update(['status' => 'expired']);
            return back()->with('error', 'Reservasi ini sudah kedaluwarsa.');
        }

        try {
            DB::transaction(function () use ($validated, $reservation, $borrowingService) {
                if ($validated['status'] === 'approved') {
                    BookReservation::where('book_id', $reservation->book_id)
                        ->where('id', '!=', $reservation->id)
                        ->whereIn('status', ['pending', 'approved'])
                        ->update(['status' => 'rejected']);

                    $reservation->update(['status' => 'approved']);

                    $borrowingService->borrow(
                        (int) $reservation->anggota_id,
                        (int) $reservation->book_id,
                        now()->toDateString(),
                        now()->addDays(BorrowingService::DEFAULT_BORROW_DAYS)->toDateString()
                    );
                } else {
                    $reservation->update(['status' => 'rejected']);
                }

                if ($reservation->user_id) {
                    NotificationHelper::send(
                        $reservation->user_id,
                        'reservation_' . $validated['status'],
                        $validated['status'] === 'approved' ? 'Reservasi disetujui' : 'Reservasi ditolak',
                        $validated['status'] === 'approved'
                            ? 'Reservasi buku "' . ($reservation->book?->judul ?? '-') . '" disetujui dan buku sudah dipinjamkan ke akun kamu.'
                            : 'Reservasi buku "' . ($reservation->book?->judul ?? '-') . '" ditolak oleh librarian.',
                        [
                            'reservation_id' => $reservation->id,
                            'status' => $validated['status'],
                        ]
                    );
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Reservasi gagal diproses: ' . $e->getMessage());
        }

        return back()->with('success', 'Reservasi berhasil ' . ($validated['status'] === 'approved' ? 'disetujui dan buku langsung dipinjamkan' : 'ditolak') . '.');
    }
}
