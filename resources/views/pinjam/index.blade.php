@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>📚 Daftar Peminjaman</h1>
    <a href="{{ route('pinjam.create') }}" class="btn-add"><span class="icon">+</span> Pinjam Buku</a>
</div>

<div class="search-filter-box">
    <form method="GET" action="{{ route('pinjam.index') }}" class="search-form">
        <input type="text" name="search" placeholder="🔍 Cari judul buku atau nama anggota..." value="{{ request('search') }}" class="search-input">
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>⏳ Dipinjam</option>
            <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>✅ Dikembalikan</option>
        </select>
        <button type="submit" class="btn-search">Cari</button>
        @if(request('search') || request('status'))
            <a href="{{ route('pinjam.index') }}" class="btn-reset">Reset</a>
        @endif
    </form>
</div>

@if(session('success'))
    <div class="alert-success">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-error">
        <span class="alert-icon">⚠️</span> {{ session('error') }}
    </div>
@endif

@if(request('search') && $pinjam->count() == 0)
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Hasil tidak ada</h3>
        <p class="text-muted">Tidak ditemukan peminjaman dengan kata kunci "{{ request('search') }}"</p>
    </div>
@elseif($pinjam->count() > 0)
    <div class="items-grid">
        @foreach($pinjam as $p)
            <div class="item-card">
                <div class="tilt-layer">
                    <div class="item-header">
                        @if($p->status == 'dipinjam')
                            <span class="status-badge status-borrowed">⏳ Dipinjam</span>
                        @else
                            <span class="status-badge status-returned">✅ Dikembalikan</span>
                        @endif
                        <span class="item-id">#{{ $p->id }}</span>
                    </div>

                    <div class="item-body">
                        <h3 class="item-title">#{{ $p->book->id_buku }} — {{ Str::limit($p->book->judul, 25) }}</h3>
                        <p class="item-user">👤 Peminjam: <strong>{{ $p->anggota->nama }}</strong></p>
                        <div class="item-dates">
                            <p>📅 Pinjam: {{ $p->tanggal_pinjam->format('d-m-Y') }}</p>
                            <p>🎯 Kembali: {{ $p->tanggal_kembali->format('d-m-Y') }}</p>
                        </div>
                    </div>

                    <div class="item-actions">
                        <a href="{{ route('pinjam.edit', $p->id) }}" class="btn-action btn-edit">✏️ Edit</a>
                        <form action="{{ route('pinjam.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus data peminjaman ini?');">
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
        <span class="text-muted">Showing {{ $pinjam->count() }} peminjaman</span>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>Belum ada peminjaman</h3>
        <p class="text-muted">Catat data peminjaman buku pertamamu!</p>
        <a href="{{ route('pinjam.create') }}" class="btn-add"><span class="icon">+</span> Pinjam Buku</a>
    </div>
@endif

<div class="bottom-spacer"></div>
@endsection

