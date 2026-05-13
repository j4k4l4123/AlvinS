@extends('layouts.app')

@section('title', 'Persetujuan Reservasi - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📌 Persetujuan Reservasi</h1>
        <p class="text-muted" style="margin-top:6px;">Setujui atau tolak pengajuan reservasi buku dari anggota.</p>
    </div>
</div>

@if($reservations->count())
    <div style="display:grid; gap:16px;">
        @foreach($reservations as $reservation)
            <div class="content-card" style="padding:20px; border:1px solid rgba(52, 211, 153, 0.18);">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div>
                        <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $reservation->book?->judul ?? '-' }}</h3>
                        <div class="text-muted">Anggota: <strong>{{ $reservation->anggota?->nama ?? '-' }}</strong></div>
                        <div class="text-muted">Rak: {{ $reservation->book?->rack?->name ?? '-' }}</div>
                        <div class="text-muted">Berlaku sampai: {{ $reservation->expires_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div style="text-align:right; min-width:220px;">
                        <span class="status-badge {{ $reservation->status === 'approved' ? 'status-available' : ($reservation->status === 'rejected' || $reservation->status === 'expired' ? 'status-borrowed' : '') }}">{{ ucfirst($reservation->status) }}</span>

                        @if($reservation->status === 'pending')
                            <div style="display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; margin-top:12px;">
                                <form method="POST" action="{{ route('reservations.update', $reservation) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn-submit">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('reservations.update', $reservation) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn-cancel">Tolak</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-info" style="margin-top:20px;">
        {{ $reservations->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📌</div>
        <h3>Belum ada reservasi</h3>
        <p class="text-muted">Semua pengajuan reservasi anggota akan muncul di sini.</p>
    </div>
@endif
@endsection
