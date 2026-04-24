<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    // Show all anggota
    public function index()
    {
        $anggota = Anggota::all();
        return view('anggota.index', compact('anggota'));
    }

    // Show create form
    public function create()
    {
        return view('anggota.create');
    }

    // Store new anggota
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_anggota' => 'required|string|max:255|unique:anggota',
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_tlp' => 'required|string|max:20',
            'tanggal_daftar' => 'required|date',
        ], [
            'required' => 'data tidak lengkap',
        ]);

        Anggota::create($validated);
        return redirect()->route('anggota.index')->with('success', 'data berhasil disimpan');
    }

    // Show single anggota
    public function show($id)
    {
        $anggota = Anggota::findOrFail($id);
        return view('anggota.show', compact('anggota'));
    }

    // Show edit form
    public function edit($id)
    {
        $anggota = Anggota::findOrFail($id);
        return view('anggota.edit', compact('anggota'));
    }

    // Update anggota
    public function update(Request $request, $id)
    {
        $anggota = Anggota::findOrFail($id);

        $validated = $request->validate([
            'id_anggota' => 'required|string|max:255|unique:anggota,id_anggota,' . $id,
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_tlp' => 'required|string|max:20',
            'tanggal_daftar' => 'required|date',
        ], [
            'required' => 'data tidak lengkap',
        ]);

        $anggota->update($validated);
        return redirect()->route('anggota.index')->with('success', 'data berhasil disimpan');
    }

    // Delete anggota
    public function destroy($id)
    {
        $anggota = Anggota::findOrFail($id);
        $anggota->delete();
        return redirect()->route('anggota.index')->with('success', 'data berhasil disimpan');
    }
}
