@extends('layouts.app')

@section('title', 'Pengajuan - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📝 Pengajuan</h1>
        <p class="text-muted">Kelola pengajuan akunmu dari satu tempat.</p>
    </div>
</div>

<div style="display:grid; gap:20px; max-width:840px;">
    @if(! auth()->user()?->isLibrarian())
        <div class="content-card" style="padding:20px; border:1px solid rgba(52,211,153,0.18); background:rgba(255,255,255,0.72);">
            <h3 style="margin-top:0; color:var(--pu-forest);">🛡️ Ajukan Jadi Librarian</h3>
            <p class="text-muted">Kalau kamu membutuhkan akses librarian, kirim pengajuan dari sini. Permintaan akan ditinjau terlebih dahulu.</p>

            @if(! $librarianRequestFeatureReady)
                <div class="alert-error" style="margin-top:16px;">
                    <span class="alert-icon">⚠️</span> Fitur permintaan librarian belum aktif karena migrasi database belum dijalankan.
                </div>
            @elseif($hasPendingLibrarianRequest)
                <div class="alert-error" style="margin-top:16px;">
                    <span class="alert-icon">⏳</span> Permintaan akses librarian kamu masih menunggu persetujuan.
                </div>
            @else
                <form method="POST" action="{{ route('librarian-registration-requests.store') }}" style="margin-top:16px;">
                    @csrf
                    <div class="form-group full-width">
                        <label for="reason">Alasan</label>
                        <textarea id="reason" name="reason" rows="4" class="form-input" placeholder="Tulis alasan kenapa kamu membutuhkan akses librarian...">{{ old('reason') }}</textarea>
                    </div>
                    <div class="form-actions" style="justify-content:flex-start; margin-top:12px;">
                        <button type="submit" class="btn-submit">Kirim Permintaan</button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    <div class="content-card" style="padding:20px; border:1px solid rgba(52,211,153,0.18); background:rgba(255,255,255,0.72);">
        <h3 style="margin-top:0; color:var(--pu-forest);">❌ Pembatalan Keanggotaan</h3>
        <p class="text-muted">Ajukan pembatalan keanggotaan jika memang sudah tidak ingin menggunakan akun member.</p>
        <div style="margin-top:12px;">
            <a href="{{ route('member.cancel-membership') }}" class="btn-action">Buka Pengajuan Pembatalan</a>
        </div>
    </div>
</div>
@endsection
