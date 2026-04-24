<?php

namespace App\Http\Controllers;

use App\Models\Pinjam;
use App\Models\Anggota;
use App\Models\Book;
use Illuminate\Http\Request;

class PinjamController extends Controller
{
    public function index(Request $request)
    {
        $query = Pinjam::with(['anggota', 'book']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('book', function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%");
            })->orWhereHas('anggota', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $pinjam = $query->get();
        return view('pinjam.index', compact('pinjam'));
    }

    public function create()
    {
        $anggota = Anggota::all();
        $books = Book::all();
        return view('pinjam.create', compact('anggota', 'books'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'book_id' => 'required|exists:books,id',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
        ], [
            'required' => 'data tidak lengkap',
            'after_or_equal' => 'Tanggal kembali harus setelah tanggal pinjam',
        ]);

        $validated['status'] = 'dipinjam';

        Pinjam::create($validated);
        return redirect()->route('pinjam.index')->with('success', 'data berhasil disimpan');
    }

    public function show($id)
    {
        $pinjam = Pinjam::with(['anggota', 'book'])->findOrFail($id);
        return view('pinjam.show', compact('pinjam'));
    }

    public function edit($id)
    {
        $pinjam = Pinjam::findOrFail($id);
        $anggota = Anggota::all();
        $books = Book::all();
        return view('pinjam.edit', compact('pinjam', 'anggota', 'books'));
    }

    public function update(Request $request, $id)
    {
        $pinjam = Pinjam::findOrFail($id);

        $validated = $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'book_id' => 'required|exists:books,id',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
        ], [
            'required' => 'data tidak lengkap',
            'after_or_equal' => 'Tanggal kembali harus setelah tanggal pinjam',
        ]);

        $pinjam->update($validated);
        return redirect()->route('pinjam.index')->with('success', 'data berhasil disimpan');
    }

    public function destroy($id)
    {
        $pinjam = Pinjam::findOrFail($id);
        $pinjam->delete();
        return redirect()->route('pinjam.index')->with('success', 'data berhasil disimpan');
    }
}
