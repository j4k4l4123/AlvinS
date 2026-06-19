@extends('layouts.app')

@section('content')
<div class="detail-container">
    <div class="detail-header">
        <div class="detail-icon">👤</div>
        <h2>Detail Anggota</h2>
        <span class="detail-id">{{ $anggota->id_anggota }}</span>
    </div>

    <div class="detail-body">
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Nama Lengkap</span>
                <span class="detail-value">{{ $anggota->nama }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nomor Telepon</span>
                <span class="detail-value">{{ $anggota->no_tlp }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Tanggal Daftar</span>
                <span class="detail-value">{{ $anggota->tanggal_daftar->format('d-m-Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Terdaftar Sejak</span>
                <span class="detail-value">{{ $anggota->created_at->format('d-m-Y H:i') }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $anggota->user?->email ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Alamat</span>
                <span class="detail-value">{{ $anggota->alamat }}</span>
            </div>
        </div>
    </div>

    <div class="detail-actions">
        <a href="{{ route('anggota.index') }}" class="btn-back">← Kembali</a>
        <a href="{{ route('anggota.edit', $anggota->id) }}" class="btn-action btn-edit">✏️ Edit</a>
        <form action="{{ route('anggota.destroy', $anggota->id) }}" method="POST" onsubmit="return confirm('Hapus anggota ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-action btn-delete">🗑️ Hapus</button>
        </form>
    </div>
</div>
@endsection

