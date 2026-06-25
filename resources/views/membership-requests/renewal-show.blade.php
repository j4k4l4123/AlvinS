@extends('layouts.app')

@section('title', 'Detail Perpanjangan - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>🔄 Detail Perpanjangan</h1>
        <p class="text-muted">Tinjau permintaan perpanjangan sebelum disetujui atau ditolak.</p>
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
        <h3 class="item-title">{{ $renewalRequest->anggota?->nama ?? $renewalRequest->user?->name ?? '-' }}</h3>
        <p class="item-detail">👤 ID Anggota: {{ $renewalRequest->anggota?->id_anggota ?? '-' }}</p>
        <p class="item-detail">📚 Buku: {{ $renewalRequest->borrowing?->book?->judul ?? '-' }}</p>
        <p class="item-detail">📅 Tanggal Pinjam: {{ $renewalRequest->borrowing?->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</p>
        <p class="item-detail">📆 Jatuh Tempo Saat Ini: {{ $renewalRequest->borrowing?->tanggal_kembali?->format('d/m/Y') ?? '-' }}</p>
        <p class="item-detail">📊 Status: {{ $renewalRequest->status === 'pending' ? 'Menunggu' : ucfirst($renewalRequest->status) }}</p>
        <p class="item-detail">🕒 Diproses: {{ $renewalRequest->processed_at?->format('d/m/Y H:i') ?? '-' }}</p>

        @if($renewalRequest->notes)
            <div style="margin-top:12px; padding:16px; border-radius:14px; background:rgba(254,242,242,0.8);">
                <div class="text-muted" style="margin-bottom:6px;">Catatan Pustakawan</div>
                <div>{{ $renewalRequest->notes }}</div>
            </div>
        @endif
    </div>

    @if($renewalRequest->status === 'pending')
        <div style="display:grid; gap:14px; margin-top:22px;">
            <form method="POST" action="{{ route('renewal-requests.update', $renewalRequest->id) }}" class="styled-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="approved">
                <div class="form-actions" style="justify-content:flex-start;">
                    <button type="submit" class="btn-submit">✅ Setujui Perpanjangan</button>
                </div>
            </form>

            <form method="POST" action="{{ route('renewal-requests.update', $renewalRequest->id) }}" class="styled-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <div class="form-group full-width">
                    <label for="notes">Catatan Penolakan</label>
                    <textarea name="notes" id="notes" rows="4" class="form-input" placeholder="Tulis alasan penolakan jika diperlukan...">{{ old('notes') }}</textarea>
                </div>
                <div class="form-actions" style="justify-content:flex-start;">
                    <button type="submit" class="btn-cancel">❌ Tolak Perpanjangan</button>
                </div>
            </form>
        </div>
    @endif

    <div style="margin-top:20px;">
        <a href="{{ route('membership-requests.renewals') }}" class="btn-back">⬅ Kembali</a>
    </div>
</div>
@endsection
