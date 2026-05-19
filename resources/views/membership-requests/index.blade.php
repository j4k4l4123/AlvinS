@extends('layouts.app')

@section('title', 'Permintaan Keanggotaan - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📝 Permintaan Keanggotaan</h1>
        <p class="text-muted">Kelola pengajuan pembatalan keanggotaan dari member.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert-success" style="margin-bottom: 16px;">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if($requests->count())
    <div class="items-grid">
        @foreach($requests as $request)
            <div class="item-card">
                <div class="tilt-layer">
                    <div class="item-header">
                        <span class="status-badge {{ $request->status === 'approved' ? 'status-available' : ($request->status === 'rejected' ? 'status-borrowed' : '') }}">
                            {{ $request->status === 'pending' ? 'Menunggu' : ucfirst($request->status) }}
                        </span>
                        <span class="item-id">#REQ-{{ str_pad((string) $request->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="item-body">
                        <h3 class="item-title">{{ $request->anggota?->nama ?? $request->user?->name ?? '-' }}</h3>
                        <p class="item-detail">👤 ID Anggota: {{ $request->anggota?->id_anggota ?? '-' }}</p>
                        <p class="item-detail">📌 Tipe: {{ $request->type === 'cancellation' ? 'Pembatalan Keanggotaan' : ucfirst($request->type) }}</p>
                        <p class="item-detail">📅 Diajukan: {{ $request->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        <p class="item-desc" style="margin-top:10px;">{{ \Illuminate\Support\Str::limit($request->reason ?? '-', 120) }}</p>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:14px;">
                            <a href="{{ route('membership-requests.show', $request->id) }}" class="btn-action">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-info" style="margin-top:20px;">
        {{ $requests->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📨</div>
        <h3>Belum ada permintaan</h3>
        <p class="text-muted">Saat ini belum ada pengajuan pembatalan keanggotaan.</p>
    </div>
@endif
@endsection
