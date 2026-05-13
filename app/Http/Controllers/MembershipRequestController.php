<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\MembershipRequest;
use App\Models\Pengembalian;
use App\Models\Pinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipRequestController extends Controller
{
    public function index()
    {
        $requests = MembershipRequest::with(['user', 'anggota', 'processedBy'])->latest()->paginate(10);

        return view('membership-requests.index', compact('requests'));
    }

    public function show($id)
    {
        $membershipRequest = MembershipRequest::with(['user', 'anggota', 'processedBy'])->findOrFail($id);

        return view('membership-requests.show', ['membershipRequest' => $membershipRequest]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        $user = auth()->user();
        $memberProfile = $user?->memberProfile;

        if (! $memberProfile) {
            return back()->withErrors(['profile' => 'Profil anggota tidak ditemukan.']);
        }

        $anggota = $user?->anggota ?? Anggota::where('id_anggota', $memberProfile->id_anggota)->first();

        if (! $anggota) {
            return back()->withErrors(['profile' => 'Data anggota tidak ditemukan.']);
        }

        $hasActiveBorrowings = Pinjam::where('anggota_id', $anggota->id)
            ->where('status', 'dipinjam')
            ->exists();

        if ($hasActiveBorrowings) {
            return back()->withErrors(['active' => 'Tidak dapat membatalkan keanggotaan karena masih ada buku yang dipinjam.']);
        }

        $hasUnpaidFines = Pengembalian::where('anggota_id', $anggota->id)
            ->where('denda', '>', 0)
            ->whereHas('fine', function ($query) {
                $query->where('status', 'unpaid');
            })
            ->exists();

        if ($hasUnpaidFines) {
            return back()->withErrors(['fine' => 'Tidak dapat membatalkan keanggotaan karena masih ada denda yang belum diselesaikan.']);
        }

        $existingPending = MembershipRequest::where('user_id', $user->id)
            ->where('type', 'cancellation')
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->withErrors(['request' => 'Permintaan pembatalan masih menunggu persetujuan pustakawan.']);
        }

        MembershipRequest::create([
            'user_id' => $user->id,
            'anggota_id' => $anggota->id,
            'type' => 'cancellation',
            'status' => 'pending',
            'reason' => $validated['reason'],
        ]);

        $memberProfile->update(['membership_status' => 'pending_cancellation']);

        return redirect()->route('member.dashboard')->with('success', 'Permintaan pembatalan keanggotaan telah diajukan.');
    }

    public function cancelOwnPending(Request $request)
    {
        $user = $request->user();

        $membershipRequest = MembershipRequest::where('user_id', $user?->id)
            ->where('type', 'cancellation')
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        DB::transaction(function () use ($membershipRequest, $user) {
            $membershipRequest->delete();
            $user?->memberProfile?->update(['membership_status' => 'active']);
        });

        return redirect()->route('member.dashboard')->with('success', 'Permintaan pembatalan keanggotaan berhasil dibatalkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $membershipRequest = MembershipRequest::with(['user.memberProfile', 'anggota.libraryCard'])->findOrFail($id);

        DB::transaction(function () use ($validated, $membershipRequest) {
            $membershipRequest->status = $validated['status'];
            $membershipRequest->processed_at = now();
            $membershipRequest->processed_by = auth()->id();
            $membershipRequest->notes = $validated['notes'] ?? null;
            $membershipRequest->save();

            $memberProfile = $membershipRequest->user?->memberProfile;
            $anggota = $membershipRequest->anggota;

            if (! $memberProfile || ! $anggota) {
                return;
            }

            if ($validated['status'] === 'approved') {
                $memberProfile->update(['membership_status' => 'cancelled']);
                $anggota->libraryCard()?->update(['status' => 'cancelled']);
                $membershipRequest->user?->delete();
            } else {
                $memberProfile->update(['membership_status' => 'active']);
            }
        });

        return redirect()->route('membership-requests.index')->with('success', 'Permintaan keanggotaan telah diproses!');
    }
}
