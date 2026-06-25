@extends('layouts.app')

@section('title', 'Detail Perpanjangan - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📄 Detail Permintaan Perpanjangan</h1>
        <p class="text-muted" style="margin-top:6px;">Review data peminjaman sebelum menyetujui atau menolak perpanjangan.</p>
    </div>
</div>

<div class="content-card" style="max-width:820px; padding:24px;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px;">
        <div style="padding:14px; border-radius:16px; background:rgba(236,253,245,0.7);">
            <div class="text-muted">Anggota</div>
            <strong>{{ $renewalRequest->anggota?->nama ?? $renewalRequest->user?->name ?? '-' }}</strong>
        </div>
        <div style="padding:14px; border-radius:16px; background:rgba(236,253,245,0.7);">
            <div class="text-muted">Buku</div>
            <strong>{{ $renewalRequest->borrowing?->book?->judul ?? '-' }}</strong>
        </div>
        <div style="padding:14px; border-radius:16px; background:rgba(236,253,245,0.7);">
            <div class="text-muted">Status</div>
            <strong>{{ ucfirst($renewalRequest->status) }}</strong>
        </div>
        <div style="padding:14px; border-radius:16px; background:rgba(236,253,245,0.7);">
            <div class="text-muted">Diajukan</div>
            <strong>{{ $renewalRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
        </div>
        <div style="padding:14px; border-radius:16px; background:rgba(236,253,245,0.7);">
            <div class="text-muted">Tanggal Pinjam</div>
            <strong>{{ $renewalRequest->borrowing?->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</strong>
        </div>
        <div style="padding:14px; border-radius:16px; background:rgba(236,253,245,0.7);">
            <div class="text-muted">Jatuh Tempo Saat Ini</div>
            <strong>{{ $renewalRequest->borrowing?->tanggal_kembali?->format('d/m/Y') ?? '-' }}</strong>
        </div>
    </div>

    @if($renewalRequest->notes)
        <div style="margin-top:18px; padding:14px 16px; border-radius:16px; background:rgba(255,255,255,0.7); border:1px solid rgba(52, 211, 153, 0.18);">
            <div class="text-muted" style="margin-bottom:6px;">Catatan Pengajuan</div>
            <div>{{ $renewalRequest->notes }}</div>
        </div>
    @endif

    @if($renewalRequest->status === 'pending')
        <div style="margin-top:20px; display:grid; gap:14px;">
            <form method="POST" action="{{ route('renewal-requests.update', $renewalRequest->id) }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="btn-submit">✅ Setujui Perpanjangan</button>
            </form>

            <form method="POST" action="{{ route('renewal-requests.update', $renewalRequest->id) }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <input type="text" name="notes" class="search-input" placeholder="Tulis alasan penolakan jika perlu" style="max-width:420px;">
                <button type="submit" class="btn-cancel">❌ Tolak</button>
            </form>
        </div>
    @endif

    <div style="margin-top:20px;">
        <a href="{{ route('renewal-requests.index') }}" class="btn-back">← Kembali</a>
    </div>
</div>
@endsection
