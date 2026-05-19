@extends('layouts.app')

@section('title', 'Batalkan Keanggotaan - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Batalkan Keanggotaan</h1>
</div>

@if($errors->any())
    <div class="alert-error" style="margin-bottom: 16px;">
        <ul style="margin:0; padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card" style="max-width:720px;">
    <p class="text-muted">Ajukan pembatalan keanggotaan. Permintaan akan diperiksa pustakawan. Pengajuan akan ditolak jika masih ada buku yang dipinjam atau denda belum selesai.</p>

    <div style="margin-top:16px; padding:14px 16px; border-radius:14px; background:rgba(254, 249, 195, 0.7); color:#854d0e;">
        Jika permintaan masih berstatus menunggu, kamu bisa membatalkannya lagi sebelum diproses librarian.
    </div>

    <form method="POST" action="{{ route('membership-requests.store') }}" style="margin-top:20px;">
        @csrf
        <div class="form-group">
            <label for="reason">Alasan Pembatalan</label>
            <textarea name="reason" id="reason" rows="5" class="search-input" required>{{ old('reason') }}</textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-submit">Kirim Permintaan</button>
            <a href="{{ route('member.dashboard') }}" class="btn-cancel">Kembali</a>
        </div>
    </form>
</div>
@endsection
