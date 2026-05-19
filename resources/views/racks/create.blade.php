@extends('layouts.app')

@section('title', 'Tambah Rak - PerpusKu')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">🗂️</div>
        <h2>Tambah Rak</h2>
        <p class="form-subtitle">Tambahkan rak baru untuk mengelompokkan lokasi buku di perpustakaan.</p>
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

    <form action="{{ route('racks.store') }}" method="POST" class="styled-form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="name"><span class="label-icon">🏷️</span> Nama Rak</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Contoh: Rak Fiksi A">
            </div>

            <div class="form-group">
                <label for="code"><span class="label-icon">🔖</span> Kode Rak</label>
                <input type="text" id="code" name="code" value="{{ old('code') }}" required class="form-input" placeholder="Contoh: RF-A">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="location_note"><span class="label-icon">📍</span> Catatan Lokasi</label>
                <input type="text" id="location_note" name="location_note" value="{{ old('location_note') }}" class="form-input" placeholder="Contoh: Lantai 1 sebelah kiri">
            </div>

            <div class="form-group">
                <label for="capacity"><span class="label-icon">📚</span> Kapasitas</label>
                <input type="number" id="capacity" name="capacity" value="{{ old('capacity', 0) }}" min="0" required class="form-input">
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('racks.index') }}" class="btn-cancel">❌ Batal</a>
            <button type="submit" class="btn-submit">
                <span class="btn-icon">💾</span>
                Simpan Rak
            </button>
        </div>
    </form>
</div>
@endsection
