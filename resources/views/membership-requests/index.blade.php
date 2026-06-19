@extends('layouts.app')

@section('title', 'Pengajuan - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📝 Pengajuan</h1>
        <p class="text-muted">Pusat semua pengajuan member.</p>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:22px;">
    <a href="{{ route('membership-requests.reservations') }}" class="content-card" style="padding:20px; text-decoration:none; color:inherit; border:1px solid rgba(52,211,153,0.18);">
        <h3 style="margin:0 0 8px; color:var(--pu-forest);">📌 Reservasi</h3>
        <p class="text-muted" style="margin:0;">Lihat dan proses pengajuan reservasi buku.</p>
    </a>
    <a href="{{ route('membership-requests.renewals') }}" class="content-card" style="padding:20px; text-decoration:none; color:inherit; border:1px solid rgba(52,211,153,0.18);">
        <h3 style="margin:0 0 8px; color:var(--pu-forest);">🔄 Perpanjangan</h3>
        <p class="text-muted" style="margin:0;">Review permintaan perpanjangan peminjaman.</p>
    </a>
    <a href="{{ route('membership-requests.cancellations') }}" class="content-card" style="padding:20px; text-decoration:none; color:inherit; border:1px solid rgba(52,211,153,0.18);">
        <h3 style="margin:0 0 8px; color:var(--pu-forest);">❌ Pembatalan Keanggotaan</h3>
        <p class="text-muted" style="margin:0;">Tinjau permintaan pembatalan keanggotaan.</p>
    </a>
</div>

@if($requests->count())
    <div style="display:grid; gap:16px;">
        @foreach($requests as $request)
            <div class="content-card" style="padding:20px; border:1px solid rgba(52, 211, 153, 0.18);">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div>
                        <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $request['title'] }}</h3>
                        <div class="text-muted">Pemohon: {{ $request['member_name'] }}</div>
                        <div class="text-muted">ID Anggota: {{ $request['member_code'] }}</div>
                        <div class="text-muted">Diajukan: {{ $request['created_at']?->format('d/m/Y H:i') ?? '-' }}</div>
                        <div class="text-muted" style="margin-top:8px;">{{ $request['description'] }}</div>
                    </div>
                    <div style="text-align:right; min-width:220px;">
                        <span class="status-badge {{ $request['status'] === 'approved' ? 'status-available' : ($request['status'] === 'rejected' ? 'status-borrowed' : '') }}">
                            {{ $request['status'] === 'pending' ? 'Menunggu' : ucfirst($request['status']) }}
                        </span>
                        <div style="margin-top:12px;">
                            <a href="{{ $request['detail_url'] }}" class="btn-action">Lihat / Proses</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-top: 16px;">{{ $requests->links() }}</div>
@else
    <div class="empty-state">
        <div class="empty-icon">📨</div>
        <h3>Belum ada pengajuan</h3>
        <p class="text-muted">Semua kategori pengajuan akan muncul dari halaman ini.</p>
    </div>
@endif
@endsection
