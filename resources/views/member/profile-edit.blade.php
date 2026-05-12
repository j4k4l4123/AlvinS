@extends('layouts.app')

@section('title', 'Edit Profil - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Edit Profil</h1>
    <p class="text-muted">Perbarui data akun dan informasi anggota kamu.</p>
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

<div class="content-card" style="max-width: 720px; padding: 24px;">
    <form method="POST" action="{{ route('member.profile.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="name">Nama</label>
            <input id="name" name="name" class="search-input" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" rows="4" class="search-input" required>{{ old('alamat', $profile->alamat ?? '') }}</textarea>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="no_tlp">No. Telepon</label>
            <input id="no_tlp" name="no_tlp" class="search-input" value="{{ old('no_tlp', $profile->no_tlp ?? '') }}" required>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn-submit">Simpan</button>
            <a href="{{ route('member.dashboard') }}" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection
