@extends('layouts.app')

@section('title', 'Permintaan Member - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📝 Permintaan Member</h1>
        <p class="text-muted">Satu tempat untuk melihat reservasi, perpanjangan, dan pembatalan keanggotaan.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert-success" style="margin-bottom: 16px;">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if($requests->count())
    <div style="display:grid; gap:16px;">
        @foreach($requests as $request)
            <div class="content-card" id="{{ $request['kind'] === 'reservation' ? 'reservation-' . $request['id'] : '' }}" style="padding:20px; border:1px solid rgba(52, 211, 153, 0.18);">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div>
                        <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $request['title'] }}</h3>
                        <div class="text-muted">Member: <strong>{{ $request['member_name'] }}</strong></div>
                        <div class="text-muted">ID Anggota: {{ $request['member_code'] }}</div>
                        <div class="text-muted">Diajukan: {{ $request['created_at']?->format('d/m/Y H:i') ?? '-' }}</div>
                        <div class="text-muted" style="margin-top:8px;">{{ $request['description'] }}</div>
                    </div>
                    <div style="text-align:right; min-width:220px;">
                        <span class="status-badge {{ $request['status'] === 'approved' ? 'status-available' : ($request['status'] === 'rejected' || $request['status'] === 'expired' ? 'status-borrowed' : '') }}">
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

    <div class="pagination-info" style="margin-top:20px;">
        {{ $requests->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📨</div>
        <h3>Belum ada permintaan</h3>
        <p class="text-muted">Reservasi, perpanjangan, dan pembatalan keanggotaan akan muncul di sini.</p>
    </div>
@endif
@endsection
