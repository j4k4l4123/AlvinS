@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>📋 Daftar Pengembalian</h1>
    <a href="{{ route('pengembalian.create') }}" class="btn-add"><span class="icon">+</span> Proses Kembali</a>
</div>

<div class="search-filter-box">
    <form method="GET" action="{{ route('pengembalian.index') }}" class="search-form">
        <input type="text" name="search" placeholder="🔍 Cari judul buku atau nama anggota..." value="{{ request('search') }}" class="search-input">
        <button type="submit" class="btn-search">Cari</button>
        @if(request('search'))
            <a href="{{ route('pengembalian.index') }}" class="btn-reset">Reset</a>
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

{{-- Search Results Section --}}
@if(request('search'))
    @if($pengembalian->count() == 0)
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>Hasil tidak ada</h3>
            <p class="text-muted">Tidak ditemukan pengembalian dengan kata kunci "{{ request('search') }}"</p>
        </div>
    @else
        <h2 style="color: #15803d; margin: 30px 0 15px; font-size: 1.3rem;">📥 Hasil Pencarian</h2>
        <div class="items-grid">
            @foreach($pengembalian as $p)
                <a href="{{ route('pengembalian.show', $p->id) }}" class="item-card" style="text-decoration: none; color: inherit;">
                    <div class="tilt-layer">
                        <div class="item-header">
                            @if($p->denda > 0)
                                <span class="status-badge status-late">⚠️ Terlambat</span>
                            @else
                                <span class="status-badge status-ontime">✅ Tepat Waktu</span>
                            @endif
                            <span class="item-id">#{{ $p->id }}</span>
                        </div>

                        <div class="item-body">
                            <h3 class="item-title">{{ Str::limit($p->book->judul, 25) }}</h3>
                            <p class="item-user">👤 {{ $p->anggota->nama }}</p>
                            <div class="item-dates">
                                <p>📅 Pinjam: {{ $p->tanggal_pinjam->format('d-m-Y') }}</p>
                                <p>🎯 Jatuh Tempo: {{ $p->tanggal_kembali->format('d-m-Y') }}</p>
                                <p>✅ Dikembalikan: {{ $p->tanggal_dikembalikan->format('d-m-Y') }}</p>
                            </div>
                            <p class="item-denda">
                                @if($p->denda > 0)
                                    <span class="denda-amount">💰 Denda: Rp {{ number_format($p->denda, 0, ',', '.') }}</span>
                                @else
                                    <span class="no-denda">✨ Tidak ada denda</span>
                                @endif
                            </p>
                        </div>

                    </div>
                </a>
            @endforeach
        </div>
    @endif
@elseif($pengembalian->count() > 0)
    {{-- Normal view: Returned Books Section --}}
    <h2 style="color: #15803d; margin: 30px 0 15px; font-size: 1.3rem;">📥 Riwayat Pengembalian</h2>
    <div class="items-grid">
        @foreach($pengembalian as $p)
            <a href="{{ route('pengembalian.show', $p->id) }}" class="item-card" style="text-decoration: none; color: inherit;">
                <div class="tilt-layer">
                    <div class="item-header">
                        @if($p->denda > 0)
                            <span class="status-badge status-late">⚠️ Terlambat</span>
                        @else
                            <span class="status-badge status-ontime">✅ Tepat Waktu</span>
                        @endif
                        <span class="item-id">#{{ $p->id }}</span>
                    </div>

                    <div class="item-body">
                        <h3 class="item-title">{{ Str::limit($p->book->judul, 25) }}</h3>
                        <p class="item-user">👤 {{ $p->anggota->nama }}</p>
                        <div class="item-dates">
                            <p>📅 Pinjam: {{ $p->tanggal_pinjam->format('d-m-Y') }}</p>
                            <p>🎯 Jatuh Tempo: {{ $p->tanggal_kembali->format('d-m-Y') }}</p>
                            <p>✅ Dikembalikan: {{ $p->tanggal_dikembalikan->format('d-m-Y') }}</p>
                        </div>
                        <p class="item-denda">
                            @if($p->denda > 0)
                                <span class="denda-amount">💰 Denda: Rp {{ number_format($p->denda, 0, ',', '.') }}</span>
                            @else
                                <span class="no-denda">✨ Tidak ada denda</span>
                            @endif
                        </p>
                    </div>

                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>Belum ada pengembalian</h3>
        <p class="text-muted">Proses pengembalian buku pertama!</p>
        <a href="{{ route('pengembalian.create') }}" class="btn-add"><span class="icon">+</span> Proses Kembali</a>
    </div>
@endif

<div class="bottom-spacer"></div>
@endsection

