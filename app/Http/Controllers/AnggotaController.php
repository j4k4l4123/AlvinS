<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRequest;
use App\Models\Anggota;
use Illuminate\Http\Request;

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

        Anggota::create($validated);
        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil ditambahkan!');
    }

    public function show($id)
    {
        $anggota = Anggota::with(['pinjam', 'pengembalian'])->findOrFail($id);
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
        $anggota = Anggota::findOrFail($id);
        $anggota->delete();
        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil dihapus!');
    }
}
