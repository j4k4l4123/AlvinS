@extends('layouts.app')

@section('title', 'Detail Pengajuan Librarian - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>🛡️ Detail Pengajuan Librarian</h1>
        <p class="text-muted">Tinjau pengajuan akses librarian sebelum disetujui atau ditolak.</p>
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
        <h3 class="item-title">{{ $librarianRegistrationRequest->user?->name ?? '-' }}</h3>
        <p class="item-detail">✉️ Email: {{ $librarianRegistrationRequest->user?->email ?? '-' }}</p>
        <p class="item-detail">📊 Status: {{ $librarianRegistrationRequest->status === 'pending' ? 'Menunggu' : ucfirst($librarianRegistrationRequest->status) }}</p>
        <p class="item-detail">📅 Diajukan: {{ $librarianRegistrationRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
        <p class="item-detail">🕒 Diproses: {{ $librarianRegistrationRequest->processed_at?->format('d/m/Y H:i') ?? '-' }}</p>

        <div style="margin-top:16px; padding:16px; border-radius:14px; background:rgba(236,253,245,0.7);">
            <div class="text-muted" style="margin-bottom:6px;">Alasan</div>
            <div>{{ $librarianRegistrationRequest->reason ?: 'Tidak ada alasan tambahan.' }}</div>
        </div>

        @if($librarianRegistrationRequest->notes)
            <div style="margin-top:12px; padding:16px; border-radius:14px; background:rgba(254,242,242,0.8);">
                <div class="text-muted" style="margin-bottom:6px;">Catatan Pustakawan</div>
                <div>{{ $librarianRegistrationRequest->notes }}</div>
            </div>
        @endif
    </div>

    @if($librarianRegistrationRequest->status === 'pending')
        <div style="display:grid; gap:14px; margin-top:22px;">
            <form method="POST" action="{{ route('librarian-registration-requests.update', $librarianRegistrationRequest) }}" class="styled-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="approved">
                <div class="form-actions" style="justify-content:flex-start;">
                    <button type="submit" class="btn-submit">✅ Setujui Pengajuan</button>
                </div>
            </form>

            <form method="POST" action="{{ route('librarian-registration-requests.update', $librarianRegistrationRequest) }}" class="styled-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <div class="form-group full-width">
                    <label for="notes">Catatan Penolakan</label>
                    <textarea name="notes" id="notes" rows="4" class="form-input" placeholder="Tulis alasan penolakan jika diperlukan...">{{ old('notes') }}</textarea>
                </div>
                <div class="form-actions" style="justify-content:flex-start;">
                    <button type="submit" class="btn-cancel">❌ Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    @endif

    <div style="margin-top:20px;">
        <a href="{{ route('membership-requests.librarian-registrations') }}" class="btn-back">⬅ Kembali</a>
    </div>
</div>
@endsection
