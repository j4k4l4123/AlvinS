<?php

namespace App\Http\Controllers;

use App\Models\BookReservation;
use Illuminate\Http\Request;

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

    public function update(Request $request, BookReservation $reservation)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        if (! in_array($reservation->status, ['pending', 'approved'], true)) {
            return back()->with('error', 'Reservasi ini sudah diproses atau tidak aktif.');
        }

        if ($reservation->expires_at && $reservation->expires_at->isPast()) {
            $reservation->update(['status' => 'expired']);
            return back()->with('error', 'Reservasi ini sudah kedaluwarsa.');
        }

        if ($validated['status'] === 'approved') {
            BookReservation::where('book_id', $reservation->book_id)
                ->where('id', '!=', $reservation->id)
                ->whereIn('status', ['pending', 'approved'])
                ->update(['status' => 'rejected']);
        }

        $reservation->update(['status' => $validated['status']]);

        return back()->with('success', 'Reservasi berhasil ' . ($validated['status'] === 'approved' ? 'disetujui' : 'ditolak') . '.');
    }
}
