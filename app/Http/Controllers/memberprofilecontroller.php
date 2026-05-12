<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberProfileUpdateRequest;

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

        $user->update([
            'name' => $validated['name'],
        ]);

        if ($profile) {
            $profile->update([
                'nama' => $validated['name'],
                'alamat' => $validated['alamat'],
                'no_tlp' => $validated['no_tlp'],
            ]);
        }

        if ($anggota) {
            $anggota->update([
                'nama' => $validated['name'],
                'alamat' => $validated['alamat'],
                'no_tlp' => $validated['no_tlp'],
            ]);
        }

        return redirect()->route('member.dashboard')->with('success', 'Profil berhasil diperbarui.');
    }
}
