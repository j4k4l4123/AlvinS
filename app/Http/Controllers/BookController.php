<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function books(Request $request)
    {
        $query = Book::with(['pinjam' => function ($q) {
            $q->where('status', 'dipinjam');
        }]);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('kategori')) {
            $query->filterByCategory($request->kategori);
        }

        $sort = $request->get('sort', 'judul');
        $order = $request->get('order', 'asc');
        $query->orderBy($sort, $order);

        $books = $query->paginate(9)->withQueryString();

        return view('books.index', compact('books'));
    }

    public function create()
    {
        $next = 1;
        $last = Book::orderBy('id', 'desc')->first();

        if ($last && preg_match('/(\d+)/', $last->id_buku, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        $nextIdBuku = 'BKU' . str_pad($next, 3, '0', STR_PAD_LEFT);
        $books = Book::orderBy('judul')->get();

        return view('books.create', compact('nextIdBuku', 'books'));
    }

    public function store(BookRequest $request)
    {
        Book::create($request->validated());
        return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan!');
    }

    public function show($id)
    {
        $book = Book::with('pinjam')->findOrFail($id);
        return view('books.show', compact('book'));
    }

    public function edit($id)
    {
        $book = Book::findOrFail($id);
        return view('books.edit', compact('book'));
    }

    public function update(BookRequest $request, $id)
    {
        $book = Book::findOrFail($id);
        $book->update($request->validated());
        return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus!');
    }
}

