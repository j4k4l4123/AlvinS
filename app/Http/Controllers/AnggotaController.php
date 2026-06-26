<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRequest;
use App\Models\Anggota;
use App\Models\MemberProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\VigenereCipherService;
use Illuminate\Http\Request;


class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->filled('search') ? (string) $request->search : null;

        $anggota = $search
            ? Anggota::search($search)
            : \Illuminate\Support\Facades\DB::table('anggota')->get();

        // Ensure we always have a Collection (not Eloquent/array), so paginator math works.
        $anggota = $anggota instanceof \Illuminate\Support\Collection ? $anggota : collect($anggota);

        $page = max((int) $request->get('page', 1), 1);
        $perPage = 10;
        $total = $anggota->count();

        $pagedItems = $anggota
            ->forPage($page, $perPage)
            ->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedItems,
            $total,
            $perPage,
            $page,
            ['path' => \Illuminate\Support\Facades\Route::currentRouteName(), 'query' => $request->query()]
        );

        return view('anggota.index', ['anggota' => $paginator->setPageName('page')]);
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
        $maxId = \Illuminate\Support\Facades\DB::table('anggota')->max('id');
        $validated['id_anggota'] = 'AGT-' . str_pad(((int) $maxId) + 1, 5, '0', STR_PAD_LEFT);
    }

    // Create user
    // Enkripsi password menggunakan Vigenère Cipher
    $vigenereService = app(VigenereCipherService::class);
    $encryptedPassword = $vigenereService->encrypt($validated['password']);

    $user = User::create([
        'name' => $validated['nama'],
        'email' => $validated['email'],
        'password' => $encryptedPassword,
    ]);

    // Assign member role
    $memberRole = Role::findByName(Role::MEMBER);

    if ($memberRole) {
        $exists = \Illuminate\Support\Facades\DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('role_id', $memberRole->id)
            ->exists();

        if (! $exists) {
            \Illuminate\Support\Facades\DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $memberRole->id,
            ]);
        }
    }

    // Prepare anggota data
    $anggotaData = $validated;

    unset(
        $anggotaData['email'],
        $anggotaData['password'],
        $anggotaData['password_confirmation']
    );

    $anggotaData['user_id'] = $user->id;

    // Insert anggota
    $anggotaId = \Illuminate\Support\Facades\DB::table('anggota')
        ->insertGetId($anggotaData);

    $anggotaRow = \Illuminate\Support\Facades\DB::table('anggota')
        ->where('id', $anggotaId)
        ->first();

    // Insert member profile
    \Illuminate\Support\Facades\DB::table('member_profiles')->insert([
        'user_id' => $user->id,
        'id_anggota' => $anggotaRow->id_anggota,
        'nama' => $anggotaRow->nama,
        'alamat' => $anggotaRow->alamat,
        'no_tlp' => $anggotaRow->no_tlp,
        'tanggal_daftar' => $anggotaRow->tanggal_daftar,
        'membership_status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()
        ->route('anggota.index')
        ->with('success', 'Anggota berhasil ditambahkan!');
}


    public function show($id)
    {
        $anggota = Anggota::find($id);
        if (!$anggota) {
            abort(404);
        }

        $anggota->user = Anggota::userFor($anggota);

        return view('anggota.show', ['anggota' => $anggota]);
    }


    public function edit($id)
    {
        $anggota = Anggota::find($id);
        if (!$anggota) {
            abort(404);
        }

        return view('anggota.edit', ['anggota' => $anggota]);
    }

    public function update(AnggotaRequest $request, $id)
    {
        $validated = $request->validated();

        $anggota = Anggota::find($id);
        if (!$anggota) {
            abort(404);
        }

        $oldIdAnggota = $anggota->id_anggota;

        \Illuminate\Support\Facades\DB::table('anggota')->where('id', $id)->update($validated);

        // Also update name in users and details in member_profiles
        $user = Anggota::userFor($anggota);
        if ($user && isset($validated['nama'])) {
            \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update([
                'name' => $validated['nama'],
                'updated_at' => now(),
            ]);
        }

        \Illuminate\Support\Facades\DB::table('member_profiles')->where('id_anggota', $oldIdAnggota)->update([
            'id_anggota' => $validated['id_anggota'],
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'],
            'no_tlp' => $validated['no_tlp'],
            'updated_at' => now(),
        ]);

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $anggota = Anggota::find($id);
        if (!$anggota) {
            abort(404);
        }

        $user = Anggota::userFor($anggota);

        // Delete user first (same logic as old code).
        if ($user) {
            if (method_exists($user, 'forceDelete')) {
                $user->forceDelete();
            } else {
                $user->delete();
            }
        }

        \Illuminate\Support\Facades\DB::table('anggota')->where('id', $id)->delete();

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil dihapus!');
    }


}
