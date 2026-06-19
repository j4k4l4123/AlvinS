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
                <label for="barcode"><span class="label-icon">🏷️</span> Barcode</label>
                <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $book->barcode) }}" class="form-input">
            </div>
            <div class="form-group">
                <label for="copy_code_prefix"><span class="label-icon">🆔</span> Prefix Kode Copy</label>
                <input type="text" id="copy_code_prefix" name="copy_code_prefix" value="{{ old('copy_code_prefix', $book->copy_code_prefix) }}" class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="judul"><span class="label-icon">📖</span> Judul Buku</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $book->judul) }}" required class="form-input">
            </div>
            <div class="form-group">
                <label for="isbn"><span class="label-icon">🔢</span> ISBN</label>
                <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="form-input">
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

        <div class="form-row">
            <div class="form-group">
                <label for="rack_id"><span class="label-icon">🗂️</span> Rak</label>
                <select id="rack_id" name="rack_id" class="form-input">
                    <option value="">Pilih Rak</option>
                    @foreach($racks as $rack)
                        <option value="{{ $rack->id }}" {{ old('rack_id', $book->rack_id) == $rack->id ? 'selected' : '' }}>{{ $rack->code }} - {{ $rack->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="language"><span class="label-icon">🌐</span> Language</label>
                <input type="text" id="language" name="language" value="{{ old('language', $book->language) }}" class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="number_of_pages"><span class="label-icon">📄</span> Number of Page</label>
                <input type="number" id="number_of_pages" name="number_of_pages" value="{{ old('number_of_pages', $book->number_of_pages) }}" min="1" class="form-input">
            </div>
        </div>


        <div class="form-row">
            <div class="form-group">
                <label for="format"><span class="label-icon">📦</span> Format</label>
                <input type="text" id="format" name="format" value="{{ old('format', $book->format) }}" class="form-input">
            </div>
            <div class="form-group">
                <label for="price"><span class="label-icon">💳</span> Harga Buku</label>
                <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $book->price ?? 0) }}" min="0" class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="daily_late_fee"><span class="label-icon">⏱️</span> Harga per Lama Peminjaman / Hari</label>
                <input type="number" step="0.01" id="daily_late_fee" name="daily_late_fee" value="{{ old('daily_late_fee', $book->daily_late_fee ?? 0) }}" min="0" class="form-input">
            </div>
            <div class="form-group">
                <label for="stock"><span class="label-icon">📚</span> Jumlah Buku</label>
                <input type="number" id="stock" name="stock" value="{{ old('stock', $book->stock ?? 1) }}" min="1" required class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="max_loan_days"><span class="label-icon">📅</span> Maks Lama Pinjam (hari)</label>
                <input type="number" id="max_loan_days" name="max_loan_days" value="{{ old('max_loan_days', $book->max_loan_days ?? 14) }}" min="1" max="60" class="form-input">
            </div>
            <div class="form-group">
                <label for="max_renewals"><span class="label-icon">🔄</span> Maks Perpanjangan</label>
                <input type="number" id="max_renewals" name="max_renewals" value="{{ old('max_renewals', $book->max_renewals ?? 1) }}" min="0" max="10" class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="copy_status"><span class="label-icon">📦</span> Status Copy</label>
                <select id="copy_status" name="copy_status" class="form-input">
                    @foreach(['available' => 'Tersedia', 'borrowed' => 'Dipinjam', 'reserved' => 'Direservasi', 'lost' => 'Hilang', 'damaged' => 'Rusak', 'maintenance' => 'Perawatan'] as $value => $label)
                        <option value="{{ $value }}" {{ old('copy_status', $book->copy_status ?? 'available') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="copy_condition"><span class="label-icon">🛠️</span> Kondisi Copy</label>
                <select id="copy_condition" name="copy_condition" class="form-input">
                    @foreach(['good' => 'Baik', 'fair' => 'Cukup', 'damaged' => 'Rusak', 'lost' => 'Hilang'] as $value => $label)
                        <option value="{{ $value }}" {{ old('copy_condition', $book->copy_condition ?? 'good') == $value ? 'selected' : '' }}>{{ $label }}</option>
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
