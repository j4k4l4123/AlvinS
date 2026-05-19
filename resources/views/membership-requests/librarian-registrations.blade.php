@extends('layouts.app')

@section('title', 'Pengajuan Librarian - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>🛡️ Pengajuan Librarian</h1>
        <p class="text-muted">Tinjau pengajuan akses librarian dari member.</p>
    </div>
</div>

@if($requests instanceof \Illuminate\Support\Collection && $requests->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">🛡️</div>
        <h3>Fitur belum aktif</h3>
        <p class="text-muted">Migrasi pengajuan librarian belum dijalankan.</p>
    </div>
@elseif($requests->count())
    <div style="display:grid; gap:16px;">
        @foreach($requests as $request)
            <div class="content-card" style="padding:20px; border:1px solid rgba(52, 211, 153, 0.18);">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div>
                        <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $request->user?->name ?? '-' }}</h3>
                        <div class="text-muted">Email: {{ $request->user?->email ?? '-' }}</div>
                        <div class="text-muted">Diajukan: {{ $request->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        <div class="text-muted" style="margin-top:8px;">{{ $request->reason ?: 'Tidak ada alasan tambahan.' }}</div>
                    </div>
                    <div style="text-align:right; min-width:220px;">
                        <span class="status-badge {{ $request->status === 'approved' ? 'status-available' : ($request->status === 'rejected' ? 'status-borrowed' : '') }}">
                            {{ $request->status === 'pending' ? 'Menunggu' : ucfirst($request->status) }}
                        </span>
                        <div style="margin-top:12px;">
                            <a href="{{ route('membership-requests.librarian-registrations.show', $request) }}" class="btn-action">Lihat / Proses</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-top: 16px;">{{ $requests->links() }}</div>
@else
    <div class="empty-state">
        <div class="empty-icon">🛡️</div>
        <h3>Belum ada pengajuan librarian</h3>
        <p class="text-muted">Saat ada member yang mengajukan akses librarian, daftar akan muncul di sini.</p>
    </div>
@endif
@endsection
