@extends('layouts.app')

@section('title', 'Permintaan Librarian - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>🛡️ Permintaan Librarian</h1>
        <p class="text-muted">Tinjau akun yang meminta akses librarian.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert-success" style="margin-bottom: 16px;">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-error" style="margin-bottom: 16px;">
        <span class="alert-icon">⚠️</span> {{ session('error') }}
    </div>
@endif

@if($requests->count())
    <div style="display:grid; gap:16px;">
        @foreach($requests as $request)
            <div class="content-card" style="padding:20px; border:1px solid rgba(52, 211, 153, 0.18);">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div>
                        <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $request->user?->name ?? '-' }}</h3>
                        <div class="text-muted">Email: {{ $request->user?->email ?? '-' }}</div>
                        <div class="text-muted">Diajukan: {{ $request->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        <div class="text-muted" style="margin-top:8px;">Alasan: {{ $request->reason ?: 'Tidak ada alasan tambahan.' }}</div>
                        @if($request->notes)
                            <div class="text-muted" style="margin-top:8px;">Catatan: {{ $request->notes }}</div>
                        @endif
                    </div>
                    <div style="text-align:right; min-width:220px;">
                        <span class="status-badge {{ $request->status === 'approved' ? 'status-available' : ($request->status === 'rejected' ? 'status-borrowed' : '') }}">
                            {{ $request->status === 'pending' ? 'Menunggu' : ucfirst($request->status) }}
                        </span>

                        @if($request->status === 'pending')
                            <div style="display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; margin-top:12px;">
                                <form method="POST" action="{{ route('librarian-registration-requests.update', $request) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn-submit">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('librarian-registration-requests.update', $request) }}" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="text" name="notes" class="search-input" placeholder="Catatan penolakan" style="min-width:180px;">
                                    <button type="submit" class="btn-cancel">Tolak</button>
                                </form>
                            </div>
                        @endif
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
        <div class="empty-icon">🛡️</div>
        <h3>Belum ada permintaan librarian</h3>
        <p class="text-muted">Pengajuan akses librarian akan muncul di sini.</p>
    </div>
@endif
@endsection
