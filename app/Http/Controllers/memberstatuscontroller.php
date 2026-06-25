<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class MemberStatusController extends Controller
{
    public function ban($anggotaId): RedirectResponse
    {
        $anggota = DB::table('anggota')->where('id', $anggotaId)->first();
        abort_if(! $anggota, 404);

        if ($anggota->user_id) {
            DB::table('member_profiles')->where('user_id', $anggota->user_id)->update([
                'membership_status' => 'banned',
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Member berhasil dibanned.');
    }

    public function unban($anggotaId): RedirectResponse
    {
        $anggota = DB::table('anggota')->where('id', $anggotaId)->first();
        abort_if(! $anggota, 404);

        if ($anggota->user_id) {
            DB::table('member_profiles')->where('user_id', $anggota->user_id)->update([
                'membership_status' => 'active',
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Member berhasil diunban.');
    }
}
