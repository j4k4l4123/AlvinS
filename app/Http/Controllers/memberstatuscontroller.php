<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\RedirectResponse;

class MemberStatusController extends Controller
{
    public function ban(Anggota $anggota): RedirectResponse
    {
        $anggota->user?->memberProfile?->update([
            'membership_status' => 'banned',
        ]);

        return back()->with('success', 'Member berhasil dibanned.');
    }

    public function unban(Anggota $anggota): RedirectResponse
    {
        $anggota->user?->memberProfile?->update([
            'membership_status' => 'active',
        ]);

        return back()->with('success', 'Member berhasil diunban.');
    }
}
