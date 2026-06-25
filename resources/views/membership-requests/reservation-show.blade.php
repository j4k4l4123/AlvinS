@extends('layouts.app')

@section('title', 'Detail Reservasi - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📌 Detail Reservasi</h1>
        <p class="text-muted">Tinjau pengajuan reservasi sebelum disetujui atau ditolak.</p>
    </div>
</div>

@if($errors->any())
    <div class="alert-error" style="margin-bottom: 16px;">
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

<div class="content-card" style="max-width:760px;">
    <div class="item-body">
        <h3 class="item-title">{{ $reservation->anggota?->nama ?? $reservation->user?->name ?? '-' }}</h3>
        <p class="item-detail">👤 ID Anggota: {{ $reservation->anggota?->id_anggota ?? '-' }}</p>
        <p class="item-detail">📚 Buku: {{ $reservation->book?->judul ?? '-' }}</p>
        <p class="item-detail">🗂️ Rak: {{ $reservation->book?->rack?->nama_rak ?? '-' }}</p>
        <p class="item-detail">📊 Status: {{ ucfirst($reservation->status) }}</p>
        <p class="item-detail">📅 Diajukan: {{ $reservation->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
        <p class="item-detail">⏳ Berlaku Sampai: {{ $reservation->expires_at?->format('d/m/Y H:i') ?? '-' }}</p>
    </div>

    @if($reservation->status === 'pending')
        <div style="display:grid; gap:14px; margin-top:22px;">
            <form method="POST" action="{{ route('reservations.update', $reservation->id) }}" class="styled-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="approved">
                <div class="form-actions" style="justify-content:flex-start;">
                    <button type="submit" class="btn-submit">✅ Setujui Reservasi</button>
                </div>
            </form>

            <form method="POST" action="{{ route('reservations.update', $reservation->id) }}" class="styled-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <div class="form-actions" style="justify-content:flex-start;">
                    <button type="submit" class="btn-cancel">❌ Tolak Reservasi</button>
                </div>
            </form>
        </div>
    @endif

    <div style="margin-top:20px;">
        <a href="{{ route('membership-requests.reservations') }}" class="btn-back">⬅ Kembali</a>
    </div>
</div>
@endsection
