@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">📖</div>
        <h2>Tambah Peminjaman</h2>
        <p class="form-subtitle">Pilih anggota dan buku yang dipinjam</p>
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

    @if(session('error'))
        <div class="alert-error">
            <span class="alert-icon">⚠️</span> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('pinjam.store') }}" method="POST" class="styled-form">
        @csrf

        {{-- Searchable Anggota Select --}}
        <div class="form-group full-width">
            <label for="anggota_search"><span class="label-icon">👤</span> Pilih Anggota</label>
            <div class="searchable-select" id="anggotaSelect">
                <input type="text" id="anggota_search" class="form-input searchable-input" placeholder="🔍 Ketik nama anggota..." autocomplete="off" onfocus="openDropdown('anggota')" oninput="filterDropdown('anggota', this.value)">
                <input type="hidden" name="anggota_id" id="anggota_id" value="{{ old('anggota_id') }}" required>
                <div class="searchable-dropdown" id="anggota_dropdown">
                    @foreach($anggota as $a)
                        <div class="searchable-option" onclick="selectOption('anggota', '{{ $a->id }}', '{{ $a->nama }} ({{ $a->id_anggota }})')">
                            {{ $a->nama }} ({{ $a->id_anggota }})
                        </div>
                    @endforeach
                </div>
                <div class="searchable-selected" id="anggota_selected">
                    @if(old('anggota_id'))
                        @php $oldA = $anggota->firstWhere('id', old('anggota_id')); @endphp
                        @if($oldA) {{ $oldA->nama }} ({{ $oldA->id_anggota }}) @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Searchable Book Select --}}
        <div class="form-group full-width">
            <label for="book_search"><span class="label-icon">📚</span> Pilih Buku</label>
            <div class="searchable-select" id="bookSelect">
                <input type="text" id="book_search" class="form-input searchable-input" placeholder="🔍 Ketik judul buku..." autocomplete="off" onfocus="openDropdown('book')" oninput="filterDropdown('book', this.value)">
                <input type="hidden" name="book_id" id="book_id" value="{{ old('book_id') }}" required>
                <div class="searchable-dropdown" id="book_dropdown">
                    @foreach($books as $b)
                        <div class="searchable-option" onclick="selectOption('book', '{{ $b->id }}', '#{{ $b->id_buku }} — {{ $b->judul }}')">
                            #{{ $b->id_buku }} — {{ $b->judul }}
                        </div>
                    @endforeach
                </div>
                <div class="searchable-selected" id="book_selected">
                    @if(old('book_id'))
                        @php $oldB = $books->firstWhere('id', old('book_id')); @endphp
                        @if($oldB) #{{ $oldB->id_buku }} — {{ $oldB->judul }} @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_pinjam"><span class="label-icon">📅</span> Tanggal Pinjam</label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="tanggal_kembali"><span class="label-icon">🎯</span> Tanggal Kembali</label>
                <input type="date" id="tanggal_kembali" name="tanggal_kembali" value="{{ old('tanggal_kembali') }}" required class="form-input">
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('pinjam.index') }}" class="btn-cancel">❌ Batal</a>
            <button type="submit" class="btn-submit">
                <span class="btn-icon">💾</span>
                Simpan
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
    .searchable-selected {
        margin-top: 8px;
        padding: 8px 14px;
        background: #dcfce7;
        border-radius: 8px;
        color: #15803d;
        font-weight: 600;
        display: none;
    }
    .searchable-selected.visible {
        display: block;
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

    function selectOption(type, id, label) {
        document.getElementById(type + '_id').value = id;
        document.getElementById(type + '_search').value = '';
        const selected = document.getElementById(type + '_selected');
        selected.textContent = '✓ ' + label;
        selected.classList.add('visible');
        closeAllDropdowns();
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (activeDropdown && !e.target.closest('#' + activeDropdown + 'Select')) {
            closeAllDropdowns();
        }
    });
</script>
@endsection

