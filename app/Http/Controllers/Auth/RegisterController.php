<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Anggota;
use App\Models\MemberProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            if ($validated['role'] === 'member') {
                $nextId = Anggota::max('id') + 1;
                $anggotaCode = 'AGT-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);

                $anggota = Anggota::create([
                    'id_anggota' => $anggotaCode,
                    'nama' => $validated['name'],
                    'alamat' => '-',
                    'no_tlp' => '-',
                    'tanggal_daftar' => now()->toDateString(),
                    'user_id' => $user->id,
                ]);

                MemberProfile::create([
                    'user_id' => $user->id,
                    'id_anggota' => $anggota->id_anggota,
                    'nama' => $validated['name'],
                    'alamat' => '-',
                    'no_tlp' => '-',
                    'tanggal_daftar' => now()->toDateString(),
                    'membership_status' => 'active',
                ]);
            }

            return $user;
        });

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }
}
