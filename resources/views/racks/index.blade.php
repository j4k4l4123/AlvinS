@extends('layouts.app')

@section('title', 'Rak Buku - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>🗂️ Rak Buku</h1>
        <p class="text-muted" style="margin-top:6px;">Lihat nomor rak dan catatan lokasi penyimpanan buku.</p>
    </div>
    <a href="{{ route('racks.create') }}" class="btn-add"><span class="icon">+</span> Tambah Rak</a>
</div>

@if(session('success'))
    <div class="alert-success" style="margin-bottom: 16px;">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if($racks->count())
    <div class="items-grid">
        @foreach($racks as $rack)
            <div class="item-card" style="padding:22px; text-decoration:none; color:inherit;">
                <div class="tilt-layer">
                    <div class="item-header">
                        <span class="status-badge">Rak {{ $rack->code }}</span>
                        <span class="item-id">{{ $rack->books_count }} judul</span>
                    </div>
                    <div class="item-body">
                        <h3 class="item-title">Nomor Rak {{ $rack->code }}</h3>
                        <p class="item-detail">📍 {{ $rack->location_note ?: 'Lokasi belum diisi' }}</p>
                        <p class="item-detail">📚 Total stok buku: {{ $rack->totalBooks() }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-info" style="margin-top:20px;">
        {{ $racks->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">🗂️</div>
        <h3>Belum ada rak</h3>
        <p class="text-muted">Tambahkan rak pertama agar lokasi buku bisa dilacak.</p>
        <a href="{{ route('racks.create') }}" class="btn-add"><span class="icon">+</span> Tambah Rak</a>
    </div>
@endif
@endsection
