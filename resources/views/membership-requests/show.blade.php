@extends('layouts.app')

@section('title', 'Detail Permintaan Keanggotaan - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📄 Detail Permintaan Keanggotaan</h1>
        <p class="text-muted">Tinjau pengajuan member sebelum disetujui atau ditolak.</p>
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
        <h3 class="item-title">{{ $membershipRequest->anggota?->nama ?? $membershipRequest->user?->name ?? '-' }}</h3>
        <p class="item-detail">👤 ID Anggota: {{ $membershipRequest->anggota?->id_anggota ?? '-' }}</p>
        <p class="item-detail">📌 Tipe: {{ $membershipRequest->type === 'cancellation' ? 'Pembatalan Keanggotaan' : ucfirst($membershipRequest->type) }}</p>
        <p class="item-detail">📊 Status: {{ $membershipRequest->status === 'pending' ? 'Menunggu' : ucfirst($membershipRequest->status) }}</p>
        <p class="item-detail">📅 Diajukan: {{ $membershipRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
        <p class="item-detail">🕒 Diproses: {{ $membershipRequest->processed_at?->format('d/m/Y H:i') ?? '-' }}</p>

        <div style="margin-top:16px; padding:16px; border-radius:14px; background:rgba(236,253,245,0.7);">
            <div class="text-muted" style="margin-bottom:6px;">Alasan Member</div>
            <div>{{ $membershipRequest->reason ?? '-' }}</div>
        </div>

        @if($membershipRequest->notes)
            <div style="margin-top:12px; padding:16px; border-radius:14px; background:rgba(254,242,242,0.8);">
                <div class="text-muted" style="margin-bottom:6px;">Catatan Pustakawan</div>
                <div>{{ $membershipRequest->notes }}</div>
            </div>
        @endif
    </div>

    @if($membershipRequest->status === 'pending')
        <div style="display:grid; gap:14px; margin-top:22px;">
            <form method="POST" action="{{ route('membership-requests.update', $membershipRequest->id) }}" class="styled-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="approved">
                <div class="form-actions" style="justify-content:flex-start;">
                    <button type="submit" class="btn-submit">✅ Setujui Permintaan</button>
                </div>
            </form>

            <form method="POST" action="{{ route('membership-requests.update', $membershipRequest->id) }}" class="styled-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <div class="form-group full-width">
                    <label for="notes">Catatan Penolakan</label>
                    <textarea name="notes" id="notes" rows="4" class="form-input" placeholder="Tulis alasan penolakan jika diperlukan...">{{ old('notes') }}</textarea>
                </div>
                <div class="form-actions" style="justify-content:flex-start;">
                    <button type="submit" class="btn-cancel">❌ Tolak Permintaan</button>
                </div>
            </form>
        </div>
    @endif

    <div style="margin-top:20px;">
        <a href="{{ route('membership-requests.index') }}" class="btn-back">⬅ Kembali</a>
    </div>
</div>
@endsection
