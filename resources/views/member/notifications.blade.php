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
            <div class="content-card" style="padding:20px; border:1px solid {{ $notification->read_at ? 'rgba(148,163,184,0.18)' : 'rgba(52,211,153,0.22)' }}; background:{{ $notification->read_at ? 'rgba(255,255,255,0.6)' : 'rgba(236,253,245,0.75)' }};">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div style="flex:1; min-width:240px;">
                        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:10px;">
                            <h3 style="margin:0; color:var(--pu-forest);">{{ $notification->title }}</h3>
                            <span class="status-badge {{ $notification->read_at ? '' : 'status-available' }}">
                                {{ $notification->read_at ? 'Dibaca' : 'Baru' }}
                            </span>
                        </div>
                        <p style="margin:0 0 10px; color:#334155; line-height:1.6;">{{ $notification->message }}</p>
                        <div class="text-muted">{{ $notification->created_at?->translatedFormat('d M Y, H:i') ?? '-' }} • {{ $notification->created_at?->diffForHumans() }}</div>
                    </div>
                    <div>
                        @if(! $notification->read_at)
                            <form method="POST" action="{{ route('member.notifications.read', $notification) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-action">Tandai Dibaca</button>
                            </form>
                        @else
                            <span class="text-muted">Selesai</span>
                        @endif
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
