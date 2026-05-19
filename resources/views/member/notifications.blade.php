@extends('layouts.app')

@section('title', 'Notifikasi - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>🔔 Notifikasi</h1>
        <p class="text-muted">Pembaruan reservasi, perpanjangan, pengembalian, pembatalan, dan status akunmu akan muncul di sini.</p>
    </div>
</div>

@if($notifications->count())
    <div style="display:grid; gap:16px;">
        @foreach($notifications as $notification)
            <div class="content-card" style="padding:20px; border:1px solid rgba(52,211,153,0.18); background:rgba(255,255,255,0.72);">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div style="flex:1; min-width:240px;">
                        <h3 style="margin:0 0 10px; color:var(--pu-forest);">{{ $notification->title }}</h3>
                        <p style="margin:0 0 10px; color:#334155; line-height:1.6;">{{ $notification->message }}</p>
                        <div class="text-muted">{{ $notification->created_at?->translatedFormat('d M Y, H:i') ?? '-' }} • {{ $notification->created_at?->diffForHumans() }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-top: 16px;">{{ $notifications->links() }}</div>
@else
    <div class="empty-state">
        <div class="empty-icon">🔔</div>
        <h3>Belum ada notifikasi</h3>
        <p class="text-muted">Saat ada update dari sistem, semuanya akan muncul di sini.</p>
    </div>
@endif
@endsection
