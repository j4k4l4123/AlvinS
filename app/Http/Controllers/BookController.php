<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function books()
    {
        $books = Book::all();
        return view('books.index', compact('books'));
    }

    public function create()
    {
        return view('books.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_buku' => 'required|string|max:255',
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
        return redirect()->route('books.index')->with('success', 'data berhasil di simpan');
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
            'id_buku' => 'required|string|max:255',
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
        return redirect()->route('books.index')->with('success', 'data berhasil di simpan');
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book deleted successfully');
    }
}