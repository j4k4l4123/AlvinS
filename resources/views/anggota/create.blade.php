@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">👤</div>
        <h2>Tambah Anggota Baru</h2>
        <p class="form-subtitle">Isi data anggota dengan lengkap</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
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

    <form action="{{ route('anggota.store') }}" method="POST" class="styled-form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="id_anggota"><span class="label-icon">🆔</span> ID Anggota</label>
                <input type="text" id="id_anggota" name="id_anggota" value="{{ old('id_anggota') }}" placeholder="Contoh: AGT001" required class="form-input">
            </div>

            <div class="form-group">
                <label for="nama"><span class="label-icon">👤</span> Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama" required class="form-input">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="no_tlp"><span class="label-icon">📞</span> Nomor Telepon</label>
                <input type="text" id="no_tlp" name="no_tlp" value="{{ old('no_tlp') }}" placeholder="08123456789" required class="form-input">
            </div>

            <div class="form-group">
                <label for="tanggal_daftar"><span class="label-icon">📅</span> Tanggal Daftar</label>
                <input type="date" id="tanggal_daftar" name="tanggal_daftar" value="{{ old('tanggal_daftar') }}" required class="form-input">
            </div>
        </div>

        <div class="form-group full-width">
            <label for="alamat"><span class="label-icon">📍</span> Alamat</label>
            <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap" required class="form-input textarea">{{ old('alamat') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('anggota.index') }}" class="btn-cancel">❌ Batal</a>
            <button type="submit" class="btn-submit">
                <span class="btn-icon">💾</span>
                Simpan Anggota
            </button>
        </div>
    </form>
</div>
@endsection

