@extends('layouts.app')

@section('title', 'Rak Buku - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>🗂️ Rak Buku</h1>
        <p class="text-muted" style="margin-top:6px;">Lihat lokasi rak dan total koleksi buku pada setiap rak.</p>
    </div>
</div>

@if($racks->count())
    <div class="items-grid">
        @foreach($racks as $rack)
            <div class="item-card" style="padding:22px; text-decoration:none; color:inherit;">
                <div class="tilt-layer">
                    <div class="item-header">
                        <span class="status-badge">{{ $rack->code }}</span>
                        <span class="item-id">{{ $rack->books_count }} judul</span>
                    </div>
                    <div class="item-body">
                        <h3 class="item-title">{{ $rack->name }}</h3>
                        <p class="item-detail">📍 {{ $rack->location_note ?: 'Lokasi belum diisi' }}</p>
                        <p class="item-detail">📚 Total stok buku: {{ $rack->totalBooks() }}</p>
                        <p class="item-detail">🧱 Kapasitas rak: {{ $rack->capacity }}</p>
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
        <p class="text-muted">Tambahkan data rak lewat database atau seeder agar lokasi buku bisa dilacak.</p>
    </div>
@endif
@endsection
