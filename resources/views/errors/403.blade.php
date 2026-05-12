@extends('layouts.app')

@section('title', '403 Forbidden')

@section('content')
<div class="content-card" style="max-width:700px; margin: 40px auto; text-align:center; padding: 32px;">
    <h1>403</h1>
    <p class="text-muted">Kamu tidak punya izin untuk membuka halaman ini.</p>
    <a href="{{ route('dashboard') }}" class="btn-submit">Kembali ke Dashboard</a>
</div>
@endsection
