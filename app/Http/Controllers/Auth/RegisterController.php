<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Anggota;
use App\Models\LibrarianRegistrationRequest;
use App\Models\MemberProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\LibraryCardService;
use App\Services\VigenereCipherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, LibraryCardService $libraryCardService, VigenereCipherService $vigenereService)
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated, $libraryCardService, $vigenereService) {
            // Enkripsi password menggunakan Vigenère Cipher sebelum disimpan ke database
            $encryptedPassword = $vigenereService->encrypt($validated['password']);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $encryptedPassword,
            ]);

            $memberRole = Role::findByName('member');
            if (!$memberRole) {
                throw new \Exception('Role member tidak ditemukan.');
            }

            $exists = DB::table('role_user')
                ->where('user_id', $user->id)
                ->where('role_id', $memberRole->id)
                ->exists();
            if (!$exists) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $memberRole->id,
                ]);
            }

            $nextId = ((int) DB::table('anggota')->max('id') ?? 0) + 1;
            $anggotaCode = 'AGT-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);

            $anggotaId = DB::table('anggota')->insertGetId([
                'id_anggota' => $anggotaCode,
                'nama' => $validated['name'],
                'alamat' => '-',
                'no_tlp' => '-',
                'tanggal_daftar' => now()->toDateString(),
                'user_id' => $user->id,
            ]);

            $anggota = DB::table('anggota')->where('id', $anggotaId)->first();

            DB::table('member_profiles')->insert([
                'user_id' => $user->id,
                'id_anggota' => $anggota->id_anggota,
                'nama' => $validated['name'],
                'alamat' => '-',
                'no_tlp' => '-',
                'tanggal_daftar' => now()->toDateString(),
                'membership_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('library_cards')->insert([
                'anggota_id' => $anggota->id,
                'user_id' => $user->id,
                'card_number' => $libraryCardService->generateSequentialCardNumber(),
                'status' => 'active',
                'issued_date' => now()->toDateString(),
                'expiry_date' => now()->addYear()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (($validated['role'] ?? 'member') === 'librarian') {
                DB::table('librarian_registration_requests')->insert([
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'reason' => $validated['reason'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $message = ($validated['role'] ?? 'member') === 'librarian'
            ? 'Registrasi berhasil! Permintaan akses librarian kamu sedang menunggu persetujuan.'
            : 'Registrasi berhasil! Kartu perpustakaan digital kamu sudah dibuat.';

        return redirect()->route('member.dashboard')->with('success', $message);
    }
}
