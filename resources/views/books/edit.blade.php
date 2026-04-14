@extends('layouts.app')

@section('content')
<h2>Edit Book</h2>

<form action="{{ route('books.update', $book->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div>
        <label for="id_buku">ID Buku:</label>
        <input type="text" id="id_buku" name="id_buku" value="{{ old('id_buku', $book->id_buku) }}" required>
        @error('id_buku') <span style="color:red;">{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label for="judul">Judul:</label>
        <input type="text" id="judul" name="judul" value="{{ old('judul', $book->judul) }}" required>
        @error('judul') <span style="color:red;">{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label for="pengarang">Pengarang:</label>
        <input type="text" id="pengarang" name="pengarang" value="{{ old('pengarang', $book->pengarang) }}" required>
        @error('pengarang') <span style="color:red;">{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label for="penerbit">Penerbit:</label>
        <input type="text" id="penerbit" name="penerbit" value="{{ old('penerbit', $book->penerbit) }}" required>
        @error('penerbit') <span style="color:red;">{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label for="thn_terbit">Tahun Terbit:</label>
        <input type="number" id="thn_terbit" name="thn_terbit" value="{{ old('thn_terbit', $book->thn_terbit) }}" required>
        @error('thn_terbit') <span style="color:red;">{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label for="kategori">Kategori:</label>
        <input type="text" id="kategori" name="kategori" value="{{ old('kategori', $book->kategori) }}" required>
        @error('kategori') <span style="color:red;">{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label for="keterangan">Keterangan (Optional):</label>
        <textarea id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $book->keterangan) }}</textarea>
    </div>
    
    <button type="submit" class="btn-primary">Update Book</button>
    <a href="{{ route('books.index') }}" class="btn" style="background-color: #6c757d;">Cancel</a>
</form>
@endsection