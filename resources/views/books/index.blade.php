@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>📚 Daftar Buku</h1>
    <a href="{{ route('books.create') }}" class="btn-add"><span class="icon">+</span> Tambah Buku</a>
</div>

<div class="search-filter-box">
    <form method="GET" action="{{ route('books.index') }}" class="search-form">
        <input type="text" name="search" placeholder="🔍 Cari judul, pengarang, atau kategori..." value="{{ request('search') }}" class="search-input">
        
        <select name="kategori" class="filter-select">
            <option value="">Semua Kategori</option>
            @php
                $kategoris = ['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Biografi', 'Lainnya'];
            @endphp
            @foreach($kategoris as $kat)
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

@if($books->count() > 0)
    <div class="items-grid">
        @foreach($books as $book)
            <div class="item-card">
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
                    <form action="{{ route('books.destroy', $book->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus buku ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action btn-delete">🗑️ Hapus</button>
                    </form>
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

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid #22c55e;
    }
    .page-header h1 {
        color: #15803d;
        margin: 0;
        font-size: 1.8rem;
    }
    .btn-add {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 12px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        transition: all 0.3s;
    }
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
    }
    .btn-add .icon {
        background: white;
        color: #22c55e;
        width: 24px;
        height: 24px;
        display: inline-block;
        border-radius: 50%;
        text-align: center;
        line-height: 24px;
        margin-right: 8px;
        font-weight: bold;
    }
    .search-filter-box {
        background: #dcfce7;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
        border-left: 4px solid #22c55e;
    }
    .search-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .search-input {
        flex: 1;
        min-width: 250px;
        padding: 12px 15px;
        border: 2px solid #86efac;
        border-radius: 25px;
        font-size: 14px;
        outline: none;
    }
    .search-input:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
    }
    .filter-select {
        padding: 12px 15px;
        border: 2px solid #86efac;
        border-radius: 25px;
        font-size: 14px;
        background: white;
        cursor: pointer;
    }
    .btn-search {
        background: #22c55e;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-reset {
        color: #6b7280;
        text-decoration: none;
        padding: 12px 15px;
        align-self: center;
    }
    .alert-success {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        border: 2px solid #22c55e;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        color: #15803d;
        font-weight: 500;
    }
    .alert-icon {
        margin-right: 10px;
    }
    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    .item-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 2px solid #dcfce7;
        transition: all 0.3s;
    }
    .item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(34, 197, 94, 0.15);
        border-color: #86efac;
    }
    .item-header {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .status-badge {
        background: #22c55e;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .item-id {
        color: #15803d;
        font-size: 13px;
        font-weight: 600;
    }
    .item-body {
        padding: 20px;
    }
    .item-title {
        color: #15803d;
        margin: 0 0 10px;
        font-size: 1.2rem;
        line-height: 1.4;
    }
    .item-detail {
        color: #6b7280;
        margin: 5px 0;
        font-size: 14px;
    }
    .item-desc {
        color: #9ca3af;
        margin: 10px 0 0;
        font-size: 13px;
        font-style: italic;
    }
    .item-actions {
        padding: 15px 20px;
        background: #f0fdf4;
        display: flex;
        gap: 10px;
    }
    .item-actions form {
        flex: 1;
        display: flex;
    }
    .btn-action {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
        width: 100%;
    }
    .btn-edit {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .btn-edit:hover {
        background: #1d4ed8;
        color: white;
    }
    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .btn-delete:hover {
        background: #dc2626;
        color: white;
    }
    .pagination-info {
        margin-top: 20px;
        text-align: center;
        padding: 15px;
        color: #6b7280;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border-radius: 20px;
        border: 3px dashed #86efac;
    }
    .empty-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }
    .empty-state h3 {
        color: #15803d;
        margin-bottom: 10px;
    }
    .text-muted {
        color: #6b7280;
    }
</style>
@endsection
