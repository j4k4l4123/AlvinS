@extends('layouts.app')

@section('title', 'Register - PerpusKu')

@section('content')
<div class="content-card" style="max-width: 640px; margin: 40px auto; padding: 24px;">
    <div class="page-header" style="margin-bottom: 20px;">
        <h1>Daftar Anggota</h1>
        <p class="text-muted">Buat akun member untuk mengakses katalog, peminjaman, dan kartu perpustakaan digital.</p>
    </div>

    @if($errors->any())
        <div class="alert-error" style="margin-bottom: 16px;">
            <ul style="margin:0; padding-left: 18px;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">
        @csrf

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="name">Nama</label>
            <input id="name" name="name" value="{{ old('name') }}" required class="search-input" />
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="search-input" />
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required class="search-input" />
            <small class="text-muted">Minimal 8 karakter, huruf besar, huruf kecil, angka, dan simbol.</small>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="search-input" />
        </div>

        <div style="display:flex; gap:12px; align-items:center;">
            <button type="submit" class="btn-submit">Daftar</button>
            <a href="{{ route('login') }}" class="btn-cancel">Kembali</a>
        </div>
    </form>
</div>
@endsection
