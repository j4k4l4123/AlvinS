@extends('layouts.app')

@section('content')
<h2>Book Details</h2>

<div style="margin-top: 20px;">
    <p><strong>ID Buku:</strong> {{ $book->id_buku }}</p>
    <p><strong>Judul:</strong> {{ $book->judul }}</p>
    <p><strong>Pengarang:</strong> {{ $book->pengarang }}</p>
    <p><strong>Penerbit:</strong> {{ $book->penerbit }}</p>
    <p><strong>Tahun Terbit:</strong> {{ $book->thn_terbit }}</p>
    <p><strong>Kategori:</strong> {{ $book->kategori }}</p>
    <p><strong>Keterangan:</strong> {{ $book->keterangan ?? '-' }}</p>
</div>

<div style="margin-top: 20px;">
    <a href="{{ route('books.edit', $book->id) }}" class="btn-primary">Edit</a>
    <a href="{{ route('books.index') }}" class="btn" style="background-color: #6c757d;">Back to List</a>
</div>
@endsection