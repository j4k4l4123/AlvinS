@extends('layouts.app')

@section('title', '401 Unauthorized')

@section('content')
<div class="content-card" style="max-width:700px; margin: 40px auto; text-align:center; padding: 32px;">
    <h1>401</h1>
    <p class="text-muted">Kamu perlu login dulu untuk mengakses halaman ini.</p>
    <a href="{{ route('login') }}" class="btn-submit">Login</a>
</div>
@endsection
