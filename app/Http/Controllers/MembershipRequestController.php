<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\MembershipRequest;
use App\Models\Pinjam;
use Illuminate\Http\Request;

class MembershipRequestController extends Controller
{
    public function index()
    {
        $requests = MembershipRequest::with(['user', 'anggota'])->latest()->paginate(10);

        return view('membership-requests.index', compact('requests'));
    }

    public function show($id)
    {
        $membershipRequest = MembershipRequest::with(['user', 'anggota'])->findOrFail($id);

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

        $anggota = Anggota::where('id_anggota', $memberProfile->id_anggota)->first();

        if (! $anggota) {
            return back()->withErrors(['profile' => 'Data anggota tidak ditemukan.']);
        }

        $hasActiveBorrowings = Pinjam::where('anggota_id', $anggota->id)
            ->where('status', 'dipinjam')
            ->exists();

        if ($hasActiveBorrowings) {
            return back()->withErrors(['active' => 'Tidak dapat membatalkan keanggotaan karena masih ada buku yang dipinjam.']);
        }

        MembershipRequest::create([
            'user_id' => $user->id,
            'anggota_id' => $anggota->id,
            'type' => 'cancellation',
            'status' => 'pending',
            'reason' => $validated['reason'],
        ]);

        return redirect()->route('member.dashboard')->with('success', 'Permintaan pembatalan keanggotaan telah diajukan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $membershipRequest = MembershipRequest::with('user')->findOrFail($id);
        $membershipRequest->status = $validated['status'];
        $membershipRequest->processed_at = now();
        $membershipRequest->processed_by = auth()->id();
        $membershipRequest->notes = $validated['notes'] ?? null;
        $membershipRequest->save();

        if ($validated['status'] === 'approved') {
            $memberProfile = $membershipRequest->user?->memberProfile;

            if ($memberProfile) {
                $memberProfile->update(['membership_status' => 'cancelled']);
            }
        }

        return redirect()->route('membership-requests.index')->with('success', 'Permintaan keanggotaan telah diproses!');
    }
}

