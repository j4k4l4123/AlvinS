@extends('layouts.app')

@section('title', 'Kartu Perpustakaan - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Kartu Perpustakaan</h1>
    <a href="{{ route('library-cards.create') }}" class="btn-add"><span class="icon">+</span> Buat Kartu</a>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($cards->count())
    <div class="items-grid">
        @foreach($cards as $card)
            <div class="item-card">
                <div class="tilt-layer">
                    <div class="item-header">
                        <span class="status-badge {{ $card->status === 'active' ? 'status-available' : 'status-borrowed' }}">
                            {{ ucfirst($card->status) }}
                        </span>
                        <span class="item-id">{{ $card->card_number }}</span>
                    </div>
                    <div class="item-body">
                        <h3 class="item-title">{{ $card->anggota?->nama ?? $card->user?->name ?? 'Anggota tidak diketahui' }}</h3>
                        <p class="item-detail">Diterbitkan: {{ $card->issued_date?->format('d/m/Y') ?? '-' }}</p>
                        <p class="item-detail">Berlaku sampai: {{ $card->expiry_date?->format('d/m/Y') ?? '-' }}</p>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
                            <a href="{{ route('library-cards.show', $card->id) }}" class="btn-action">Lihat</a>
                            <form method="POST" action="{{ route('library-cards.toggle', $card->id) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-action">Ubah Status</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-info" style="margin-top:20px;">
        {{ $cards->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">💳</div>
        <h3>Belum ada kartu perpustakaan</h3>
        <p class="text-muted">Buat kartu pertama untuk anggota.</p>
    </div>
@endif
@endsection
