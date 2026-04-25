@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>📚 Daftar Buku</h1>
    <a href="{{ route('books.create') }}" class="btn-add"><span class="icon">+</span> Tambah Buku</a>
</div>

<div class="search-filter-box">
    <form method="GET" action="{{ route('books.index') }}" class="search-form">
        <input type="text" name="search" placeholder="🔍 Cari judul, pengarang, atau kategori..." value="{{ request('search') }}" class="search-input">

        <select name="kategori" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach(['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Biografi', 'Lainnya'] as $kat)
                <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
            @endforeach
        </select>

        <button type="submit" class="btn-search">Cari</button>
        @if(request('search') || request('kategori'))
            <a href="{{ route('books.index') }}" class="btn-reset">Reset</a>
        @endif
    </form>
</div>

@if(session('success'))
    <div class="alert-success">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if((request('search') || request('kategori')) && $books->count() == 0)
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Hasil tidak ada</h3>
        <p class="text-muted">Tidak ditemukan buku dengan kata kunci "{{ request('search') }}"</p>
    </div>
@elseif($books->count() > 0)
    <div class="items-grid">
        @foreach($books as $book)
            <div class="item-card">
                <div class="tilt-layer">
                    <div class="item-header">
                        <span class="status-badge">{{ $book->kategori }}</span>
                        <span class="item-id">#{{ $book->id_buku }}</span>
                    </div>
                    <div class="item-body">
                        <h3 class="item-title">{{ $book->judul }}</h3>
                        <p class="item-detail">✍️ {{ $book->pengarang }}</p>
                        <p class="item-detail">🏢 {{ $book->penerbit }}, {{ $book->thn_terbit }}</p>
                        @if($book->keterangan)
                            <p class="item-desc">{{ Str::limit($book->keterangan, 60) }}</p>
                        @endif
                    </div>
                    <div class="item-actions">
                        <a href="{{ route('books.edit', $book->id) }}" class="btn-action btn-edit">✏️ Edit</a>
                        <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Hapus buku ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete">🗑️ Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-info">
        <span class="text-muted">Showing {{ $books->firstItem() ?? 0 }} to {{ $books->lastItem() ?? 0 }} of {{ $books->total() }} books</span>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3>Belum ada buku</h3>
        <p class="text-muted">Tambahkan buku pertama ke perpustakaan!</p>
        <a href="{{ route('books.create') }}" class="btn-add"><span class="icon">+</span> Tambah Buku</a>
    </div>
@endif
@endsection
