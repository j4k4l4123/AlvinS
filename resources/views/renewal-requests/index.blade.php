@extends('layouts.app')

@section('title', 'Permintaan Perpanjangan - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>🔄 Permintaan Perpanjangan</h1>
        <p class="text-muted" style="margin-top:6px;">Kelola permintaan perpanjangan peminjaman dari anggota dengan tampilan yang lebih ringkas.</p>
    </div>
</div>

@if($requests->count())
    <div style="display:grid; gap:16px;">
        @foreach($requests as $request)
            <div class="content-card" style="padding:20px; border:1px solid rgba(52, 211, 153, 0.18);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                    <div>
                        <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $request->borrowing?->book?->judul ?? '-' }}</h3>
                        <div class="text-muted">Member: <strong>{{ $request->anggota?->nama ?? $request->user?->name ?? '-' }}</strong></div>
                        <div class="text-muted">Diajukan: {{ $request->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        <div class="text-muted">Jatuh tempo saat ini: {{ $request->borrowing?->tanggal_kembali?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                    <div style="text-align:right; min-width:180px;">
                        <span class="status-badge {{ $request->status === 'approved' ? 'status-available' : ($request->status === 'rejected' ? 'status-borrowed' : '') }}">{{ ucfirst($request->status) }}</span>
                        <div style="margin-top:12px;">
                            <a href="{{ route('renewal-requests.show', $request) }}" class="btn-action btn-view">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                @if($request->notes)
                    <div style="margin-top:14px; padding:12px 14px; border-radius:14px; background:rgba(236,253,245,0.7); color:#334155;">
                        {{ $request->notes }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="pagination-info" style="margin-top:20px;">
        {{ $requests->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">🔄</div>
        <h3>Belum ada permintaan perpanjangan</h3>
        <p class="text-muted">Semua pengajuan perpanjangan anggota akan muncul di sini.</p>
    </div>
@endif
@endsection
