@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">✏️</div>
        <h2>Edit Buku</h2>
        <p class="form-subtitle">Update informasi {{ Str::limit($book->judul, 30) }}</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <span class="alert-icon">⚠️</span>
            <div>
                <strong>Oops! Ada kesalahan:</strong>
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('books.update', $book->id) }}" method="POST" class="styled-form">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label for="id_buku"><span class="label-icon">🆔</span> ID Buku</label>
                <input type="text" id="id_buku" name="id_buku" value="{{ old('id_buku', $book->id_buku) }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="judul"><span class="label-icon">📖</span> Judul Buku</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $book->judul) }}" required class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="pengarang"><span class="label-icon">✍️</span> Pengarang</label>
                <input type="text" id="pengarang" name="pengarang" value="{{ old('pengarang', $book->pengarang) }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="penerbit"><span class="label-icon">🏢</span> Penerbit</label>
                <input type="text" id="penerbit" name="penerbit" value="{{ old('penerbit', $book->penerbit) }}" required class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="thn_terbit"><span class="label-icon">📅</span> Tahun Terbit</label>
                <input type="number" id="thn_terbit" name="thn_terbit" value="{{ old('thn_terbit', $book->thn_terbit) }}" min="1900" max="{{ date('Y') + 1 }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="kategori"><span class="label-icon">🏷️</span> Kategori</label>
                <select id="kategori" name="kategori" required class="form-input">
                    @foreach(['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Biografi', 'Lainnya'] as $kat)
                        <option value="{{ $kat }}" {{ old('kategori', $book->kategori) == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group full-width">
            <label for="keterangan"><span class="label-icon">📝</span> Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="3" class="form-input textarea">{{ old('keterangan', $book->keterangan) }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('books.index') }}" class="btn-cancel">❌ Batal</a>
            <button type="submit" class="btn-submit">
                <span class="btn-icon">💾</span>
                Update Buku
            </button>
        </div>
    </form>
</div>
@endsection

