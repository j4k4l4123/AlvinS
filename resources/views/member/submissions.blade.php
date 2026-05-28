@extends('layouts.app')

@section('title', 'Pengajuan - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>📝 Pengajuan</h1>
        <p class="text-muted">Kelola pengajuan membership dari satu tempat.</p>
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

<div style="display:grid; gap:20px; max-width:840px;">
    <div class="content-card" style="padding:20px; border:1px solid rgba(52,211,153,0.18); background:rgba(255,255,255,0.72);">
        <h3 style="margin-top:0; color:var(--pu-forest);">🔁 Perpanjangan Membership</h3>
        <p class="text-muted">Ajukan perpanjangan masa aktif membership ke librarian.</p>

        <form method="POST" action="{{ route('membership-renewals.store') }}" style="margin-top:16px;">
            @csrf
            <div class="form-group full-width">
                <label for="reason">Alasan (opsional)</label>
                <textarea id="reason" name="reason" rows="4" class="form-input" placeholder="Contoh: ingin memperpanjang masa aktif kartu perpustakaan saya.">{{ old('reason') }}</textarea>
            </div>
            <div class="form-actions" style="justify-content:flex-start; margin-top:12px;">
                <button type="submit" class="btn-submit">Kirim Pengajuan</button>
            </div>
        </form>
    </div>

    <div class="content-card" style="padding:20px; border:1px solid rgba(52,211,153,0.18); background:rgba(255,255,255,0.72);">
        <h3 style="margin-top:0; color:var(--pu-forest);">❌ Pembatalan Keanggotaan</h3>
        <p class="text-muted">Ajukan pembatalan keanggotaan jika memang sudah tidak ingin menggunakan akun member.</p>
        <div style="margin-top:12px;">
            <a href="{{ route('member.cancel-membership') }}" class="btn-action">Buka Pengajuan Pembatalan</a>
        </div>
    </div>
</div>
@endsection
