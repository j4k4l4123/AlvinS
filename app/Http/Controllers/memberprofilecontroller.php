<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberProfileUpdateRequest;
use Illuminate\Support\Facades\DB;

class MemberProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $profile = $user?->memberProfile;

        return view('member.profile-edit', compact('user', 'profile'));
    }

    public function update(MemberProfileUpdateRequest $request)
    {
        $user = auth()->user();
        $profile = $user?->memberProfile;
        $anggota = $user?->anggota;
        $validated = $request->validated();

        if ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'name' => $validated['name'],
            ]);
        }

        if ($profile) {
            DB::table('member_profiles')->where('id', $profile->id)->update([
                'nama' => $validated['name'],
                'alamat' => $validated['alamat'],
                'no_tlp' => $validated['no_tlp'],
                'updated_at' => now(),
            ]);
        }

        if ($anggota) {
            DB::table('anggota')->where('id', $anggota->id)->update([
                'nama' => $validated['name'],
                'alamat' => $validated['alamat'],
                'no_tlp' => $validated['no_tlp'],
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('member.dashboard')->with('success', 'Profil berhasil diperbarui.');
    }
}
