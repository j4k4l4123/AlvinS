@extends('layouts.app')

@section('title', 'Cancel Membership - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Cancel Membership</h1>
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

    <form method="POST" action="{{ route('membership-requests.store') }}" style="margin-top:20px;">
        @csrf
        <div class="form-group">
            <label for="reason">Alasan</label>
            <textarea name="reason" id="reason" rows="5" class="search-input" required>{{ old('reason') }}</textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-submit">Submit Request</button>
            <a href="{{ route('member.dashboard') }}" class="btn-cancel">Back</a>
        </div>
    </form>
</div>
@endsection
