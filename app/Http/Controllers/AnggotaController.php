<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRequest;
use App\Models\Anggota;
use App\Models\MemberProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $anggotaQuery = Anggota::query();

        if ($request->filled('search')) {
            $anggotaQuery->search($request->search);
        }

        $anggota = $anggotaQuery->paginate(10)->withQueryString();
        return view('anggota.index', compact('anggota'));
    }

    public function create()
    {
        return view('anggota.create');
    }

    public function store(AnggotaRequest $request)
    {
        $validated = $request->validated();

        // Generate sequential ID if not provided
        if (empty($validated['id_anggota'])) {
            $maxId = Anggota::max('id');
            $validated['id_anggota'] = 'AGT-' . str_pad(((int)$maxId) + 1, 5, '0', STR_PAD_LEFT);
        }

        $userData = [
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        $user = User::create($userData);

        $memberRole = Role::where('name', Role::MEMBER)->first();
        if ($memberRole) {
            $user->roles()->syncWithoutDetaching([$memberRole->id]);
        }

        $anggotaData = $validated;
        unset($anggotaData['email'], $anggotaData['password'], $anggotaData['password_confirmation']);

        $anggotaData['user_id'] = $user->id;
        $anggota = Anggota::create($anggotaData);

        MemberProfile::create([
            'user_id' => $user->id,
            'id_anggota' => $anggota->id_anggota,
            'nama' => $anggota->nama,
            'alamat' => $anggota->alamat,
            'no_tlp' => $anggota->no_tlp,
            'tanggal_daftar' => $anggota->tanggal_daftar,
            'membership_status' => 'active',
        ]);

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil ditambahkan!');
    }


    public function show($id)
    {
        $anggota = Anggota::with(['pinjam', 'pengembalian', 'user'])->findOrFail($id);
        return view('anggota.show', compact('anggota'));
    }


    public function edit($id)
    {
        $anggota = Anggota::findOrFail($id);
        return view('anggota.edit', compact('anggota'));
    }

    public function update(AnggotaRequest $request, $id)
    {
        $anggota = Anggota::findOrFail($id);
        $anggota->update($request->validated());
        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $anggota = Anggota::with('user')->findOrFail($id);

        // Pastikan record user terkait ikut terhapus agar email tidak dianggap masih dipakai.
        // Pakai forceDelete agar pasti hilang dari tabel users jika model memakai soft delete.
        if ($anggota->user) {
            if (method_exists($anggota->user, 'forceDelete')) {
                $anggota->user->forceDelete();
            } else {
                $anggota->user->delete();
            }
        }

        if (method_exists($anggota, 'forceDelete')) {
            $anggota->forceDelete();
        } else {
            $anggota->delete();
        }

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil dihapus!');
    }


}
