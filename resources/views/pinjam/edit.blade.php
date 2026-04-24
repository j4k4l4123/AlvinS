@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">✏️</div>
        <h2>Edit Peminjaman</h2>
        <p class="form-subtitle">Update data peminjaman #{{ $pinjam->id }}</p>
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

    <form action="{{ route('pinjam.update', $pinjam->id) }}" method="POST" class="styled-form">
        @csrf
        @method('PUT')

        {{-- Anggota (read-only) --}}
        <div class="form-group full-width">
            <label><span class="label-icon">👤</span> Anggota</label>
            <div class="form-static">{{ $pinjam->anggota->nama }} ({{ $pinjam->anggota->id_anggota }})</div>
            <input type="hidden" name="anggota_id" value="{{ $pinjam->anggota_id }}">
        </div>

        {{-- Buku (read-only) --}}
        <div class="form-group full-width">
            <label><span class="label-icon">📚</span> Buku</label>
            <div class="form-static">{{ $pinjam->book->judul }}</div>
            <input type="hidden" name="book_id" value="{{ $pinjam->book_id }}">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_pinjam"><span class="label-icon">📅</span> Tanggal Pinjam</label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', $pinjam->tanggal_pinjam->format('Y-m-d')) }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="tanggal_kembali"><span class="label-icon">🎯</span> Tanggal Kembali</label>
                <input type="date" id="tanggal_kembali" name="tanggal_kembali" value="{{ old('tanggal_kembali', $pinjam->tanggal_kembali->format('Y-m-d')) }}" required class="form-input">
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('pinjam.index') }}" class="btn-cancel">❌ Batal</a>
            <button type="submit" class="btn-submit">
                <span class="btn-icon">💾</span>
                Update
            </button>
        </div>
    </form>
</div>
@endsection

