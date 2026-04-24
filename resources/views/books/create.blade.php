@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">📚</div>
        <h2>Tambah Buku Baru</h2>
        <p class="form-subtitle">Lengkapi informasi buku</p>
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

    {{-- Searchable Existing Book Select --}}
    <div class="form-group full-width" style="margin-bottom:25px;">
        <label for="existing_book_search"><span class="label-icon">🔍</span> Salin dari Buku yang Sudah Ada (Opsional)</label>
        <div class="searchable-select" id="existingBookSelect">
            <input type="text" id="existing_book_search" class="form-input searchable-input" placeholder="Ketik judul buku yang sudah ada untuk salin data..." autocomplete="off" onfocus="openDropdown('existing_book')" oninput="filterDropdown('existing_book', this.value)">
            <div class="searchable-dropdown" id="existing_book_dropdown">
                @foreach($books as $b)
                    <div class="searchable-option"
                         data-judul="{{ $b->judul }}"
                         data-pengarang="{{ $b->pengarang }}"
                         data-penerbit="{{ $b->penerbit }}"
                         data-thn="{{ $b->thn_terbit }}"
                         data-kategori="{{ $b->kategori }}"
                         data-keterangan="{{ $b->keterangan }}"
                         onclick="selectExistingBook(this)">
                        #{{ $b->id_buku }} — {{ $b->judul }}
                    </div>
                @endforeach
            </div>
        </div>
        <p style="font-size:12px;color:#6b7280;margin-top:6px;">Pilih buku yang sudah ada untuk mengisi otomatis semua field kecuali ID Buku.</p>
    </div>

    <form action="{{ route('books.store') }}" method="POST" class="styled-form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="id_buku"><span class="label-icon">🆔</span> ID Buku</label>
                <input type="text" id="id_buku" name="id_buku" value="{{ old('id_buku', $nextIdBuku) }}" readonly required class="form-input" style="background:#f0fdf4; color:#15803d; font-weight:600;">
            </div>

            <div class="form-group">
                <label for="judul"><span class="label-icon">📖</span> Judul Buku</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" placeholder="Judul lengkap" required class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="pengarang"><span class="label-icon">✍️</span> Pengarang</label>
                <input type="text" id="pengarang" name="pengarang" value="{{ old('pengarang') }}" placeholder="Nama pengarang" required class="form-input">
            </div>

            <div class="form-group">
                <label for="penerbit"><span class="label-icon">🏢</span> Penerbit</label>
                <input type="text" id="penerbit" name="penerbit" value="{{ old('penerbit') }}" placeholder="Nama penerbit" required class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="thn_terbit"><span class="label-icon">📅</span> Tahun Terbit</label>
                <input type="number" id="thn_terbit" name="thn_terbit" value="{{ old('thn_terbit') }}" placeholder="2024" min="1900" max="{{ date('Y') + 1 }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="kategori"><span class="label-icon">🏷️</span> Kategori</label>
                <select id="kategori" name="kategori" required class="form-input">
                    <option value="">Pilih Kategori</option>
                    @foreach(['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Biografi', 'Lainnya'] as $kat)
                        <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group full-width">
            <label for="keterangan"><span class="label-icon">📝</span> Keterangan (Opsional)</label>
            <textarea id="keterangan" name="keterangan" rows="3" placeholder="Deskripsi singkat tentang buku" class="form-input textarea">{{ old('keterangan') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('books.index') }}" class="btn-cancel">❌ Batal</a>
            <button type="submit" class="btn-submit">
                <span class="btn-icon">💾</span>
                Simpan Buku
            </button>
        </div>
    </form>
</div>

<style>
    .searchable-select {
        position: relative;
    }
    .searchable-input {
        width: 100%;
        cursor: pointer;
    }
    .searchable-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 250px;
        overflow-y: auto;
        background: white;
        border: 2px solid #bbf7d0;
        border-top: none;
        border-radius: 0 0 12px 12px;
        z-index: 50;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .searchable-dropdown.open {
        display: block;
    }
    .searchable-option {
        padding: 12px 18px;
        cursor: pointer;
        border-bottom: 1px solid #f0fdf4;
        transition: background 0.15s;
        color: #374151;
    }
    .searchable-option:hover {
        background: #dcfce7;
    }
    .searchable-option.hidden {
        display: none;
    }
</style>

<script>
    let activeDropdown = null;

    function openDropdown(type) {
        closeAllDropdowns();
        document.getElementById(type + '_dropdown').classList.add('open');
        activeDropdown = type;
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.searchable-dropdown').forEach(d => d.classList.remove('open'));
        activeDropdown = null;
    }

    function filterDropdown(type, query) {
        const dropdown = document.getElementById(type + '_dropdown');
        const options = dropdown.querySelectorAll('.searchable-option');
        query = query.toLowerCase();
        options.forEach(opt => {
            if (opt.textContent.toLowerCase().includes(query)) {
                opt.classList.remove('hidden');
            } else {
                opt.classList.add('hidden');
            }
        });
    }

    function selectExistingBook(el) {
        document.getElementById('judul').value = el.dataset.judul;
        document.getElementById('pengarang').value = el.dataset.pengarang;
        document.getElementById('penerbit').value = el.dataset.penerbit;
        document.getElementById('thn_terbit').value = el.dataset.thn;
        document.getElementById('kategori').value = el.dataset.kategori;
        document.getElementById('keterangan').value = el.dataset.keterangan;
        document.getElementById('existing_book_search').value = el.textContent.trim();
        closeAllDropdowns();
    }

    document.addEventListener('click', function(e) {
        if (activeDropdown && !e.target.closest('#existingBookSelect')) {
            closeAllDropdowns();
        }
    });
</script>
@endsection

