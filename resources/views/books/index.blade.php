@extends('layouts.app')

@section('content')
<a href="{{ route('books.create') }}" class="btn">+ Add New Book</a>

@if($books->count() > 0)
    <table>
        <thead>
            <tr>
                <th>ID Buku</th>
                <th>Judul</th>
                <th>Pengarang</th>
                <th>Penerbit</th>
                <th>Tahun Terbit</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($books as $book)
                <tr>
                    <td>{{ $book->id_buku }}</td>
                    <td>{{ $book->judul }}</td>
                    <td>{{ $book->pengarang }}</td>
                    <td>{{ $book->penerbit }}</td>
                    <td>{{ $book->thn_terbit }}</td>
                    <td>{{ $book->kategori }}</td>
                    <td>{{ $book->keterangan ?? '-' }}</td>
                    <td>
                        <a href="{{ route('books.edit', $book->id) }}" class="btn-primary" style="padding: 5px 10px;">Edit</a>
                        <form action="{{ route('books.destroy', $book->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No books yet. <a href="{{ route('books.create') }}">Add your first book!</a></p>
@endif
@endsection