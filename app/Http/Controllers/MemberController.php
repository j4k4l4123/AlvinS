<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\MemberProfile;
use App\Models\Pinjam;

class MemberController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $profile = $user?->memberProfile;

        $anggota = null;
        if ($profile?->id_anggota) {
            $anggota = Anggota::where('id_anggota', $profile->id_anggota)->first();
        }

        $activeBorrowings = $anggota
            ? Pinjam::with(['book', 'pengembalian'])
                ->where('anggota_id', $anggota->id)
                ->where('status', 'dipinjam')
                ->latest()
                ->get()
            : collect();

        $borrowingHistory = $anggota
            ? Pinjam::with(['book', 'pengembalian'])
                ->where('anggota_id', $anggota->id)
                ->latest()
                ->get()
            : collect();

        return view('member.dashboard', compact('profile', 'activeBorrowings', 'borrowingHistory'));
    }

    public function show($id)
    {
        $profile = MemberProfile::findOrFail($id);

        return view('member.show', compact('profile'));
    }
}
