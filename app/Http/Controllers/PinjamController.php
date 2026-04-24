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
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereHas('book', function($sub) use ($search) {
                    $sub->whereRaw('LOWER(judul) LIKE ?', ["%{$search}%"]);
                })->orWhereHas('anggota', function($sub) use ($search) {
                    $sub->whereRaw('LOWER(nama) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pinjam = $query->get();
        return view('pinjam.index', compact('pinjam'));
    }

    public function create()
    {
        $anggota = Anggota::all();
        // Only show books that are NOT currently borrowed
        $borrowedBookIds = Pinjam::where('status', 'dipinjam')->pluck('book_id')->toArray();
        $books = Book::whereNotIn('id', $borrowedBookIds)->get();
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

        // Check if book is already borrowed
        $alreadyBorrowed = Pinjam::where('book_id', $validated['book_id'])
            ->where('status', 'dipinjam')
            ->exists();

        if ($alreadyBorrowed) {
            return redirect()->back()->withInput()->with('error', 'Buku ini sedang dipinjam oleh orang lain!');
        }

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
