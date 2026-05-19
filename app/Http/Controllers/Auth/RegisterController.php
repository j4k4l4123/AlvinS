<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Anggota;
use App\Models\MemberProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\LibraryCardService;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, LibraryCardService $libraryCardService)
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated, $libraryCardService) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $memberRole = Role::where('name', 'member')->firstOrFail();
            $user->roles()->sync([$memberRole->id]);

            $nextId = (Anggota::max('id') ?? 0) + 1;
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

            $anggota->libraryCard()->create([
                'user_id' => $user->id,
                'card_number' => $libraryCardService->generateSequentialCardNumber(),
                'status' => 'active',
                'issued_date' => now()->toDateString(),
                'expiry_date' => now()->addYear()->toDateString(),
            ]);

            return $user;
        });

        return redirect()->route('anggota.index')->with('success', 'Registrasi anggota berhasil! Kartu perpustakaan digital sudah dibuat.');
    }
}
