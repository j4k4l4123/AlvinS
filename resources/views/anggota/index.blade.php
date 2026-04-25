@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>👥 Daftar Anggota</h1>
    <a href="{{ route('anggota.create') }}" class="btn-add"><span class="icon">+</span> Tambah Anggota</a>
</div>

<div class="search-filter-box">
    <form method="GET" action="{{ route('anggota.index') }}" class="search-form">
        <input type="text" name="search" placeholder="🔍 Cari nama, ID, atau alamat..." value="{{ request('search') }}" class="search-input">
        <button type="submit" class="btn-search">Cari</button>
        @if(request('search'))
            <a href="{{ route('anggota.index') }}" class="btn-reset">Reset</a>
        @endif
    </form>
</div>

@if(session('success'))
    <div class="alert-success">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if(request('search') && $anggota->count() == 0)
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Hasil tidak ada</h3>
        <p class="text-muted">Tidak ditemukan anggota dengan kata kunci "{{ request('search') }}"</p>
    </div>
@elseif($anggota->count() > 0)
    <div class="items-grid">
        @foreach($anggota as $a)
            <div class="item-card">
                <div class="tilt-layer">
                    <div class="item-header">
                        <span class="status-badge">{{ $a->id_anggota }}</span>
                        <span class="item-id">{{ $a->tanggal_daftar ? $a->tanggal_daftar->format('d-m-Y') : '-' }}</span>
                    </div>

                    <div class="item-body">
                        <h3 class="item-title">{{ $a->nama }}</h3>
                        <p class="item-detail">📞 {{ $a->no_tlp ?? '-' }}</p>
                        <p class="item-detail">📍 {{ Str::limit($a->alamat ?? '-', 40) }}</p>
                    </div>

                    <div class="item-actions">
                        <a href="{{ route('anggota.edit', $a->id) }}" class="btn-action btn-edit">✏️ Edit</a>
                        <form action="{{ route('anggota.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus anggota ini?');">
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
        <span class="text-muted">Showing {{ $anggota->count() }} anggota</span>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>Belum ada anggota</h3>
        <p class="text-muted">Tambahkan anggota pertama Anda!</p>
        <a href="{{ route('anggota.create') }}" class="btn-add"><span class="icon">+</span> Tambah Anggota</a>
    </div>
@endif
@endsection

