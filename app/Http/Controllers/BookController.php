<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function books(Request $request)
    {
        $query = Book::with(['pinjam' => function ($q) {
            $q->where('status', 'dipinjam');
        }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(judul) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(pengarang) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(kategori) LIKE ?', ["%{$search}%"]);
            });
        }

        // Filter by category
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Sorting
        $sort = $request->get('sort', 'judul');
        $order = $request->get('order', 'asc');
        $query->orderBy($sort, $order);

        $books = $query->paginate(9)->withQueryString();
        
        return view('books.index', compact('books'));
    }

    public function create()
    {
        $books = Book::all();

        $nextNum = 1;
        $lastBook = Book::orderBy('id', 'desc')->first();
        if ($lastBook && preg_match('/(\d+)/', $lastBook->id_buku, $matches)) {
            $nextNum = (int) $matches[1] + 1;
        }
        $nextIdBuku = 'BKU' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        return view('books.create', compact('books', 'nextIdBuku'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_buku' => 'required|string|max:255|unique:books',
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'thn_terbit' => 'required|integer',
            'kategori' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ], [
            'required' => 'data tidak lengkap',
        ]);

        Book::create($validated);
        return redirect()->route('books.index')->with('success', 'data berhasil disimpan');
    }

    public function show($id)
    {
        $book = Book::findOrFail($id);
        return view('books.show', compact('book'));
    }

    public function edit($id)
    {
        $book = Book::findOrFail($id);
        return view('books.edit', compact('book'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_buku' => 'required|string|max:255|unique:books,id_buku,' . $id,
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'thn_terbit' => 'required|integer',
            'kategori' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ], [
            'required' => 'data tidak lengkap',
        ]);

        $book = Book::findOrFail($id);
        $book->update($validated);
        return redirect()->route('books.index')->with('success', 'data berhasil disimpan');
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();
        return redirect()->route('books.index')->with('success', 'data berhasil disimpan');
    }
}
