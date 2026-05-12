@extends('layouts.app')

@section('title', '404 Not Found')

@section('content')
<div class="content-card" style="max-width:700px; margin: 40px auto; text-align:center; padding: 32px;">
    <h1>404</h1>
    <p class="text-muted">Halaman yang kamu cari tidak ditemukan.</p>
    <a href="{{ route('dashboard') }}" class="btn-submit">Kembali ke Dashboard</a>
</div>
@endsection
