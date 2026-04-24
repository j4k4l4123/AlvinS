@extends('layouts.app')

@section('content')
<div class="detail-container">
    <div class="detail-header">
        <div class="detail-icon">📥</div>
        <h2>Detail Pengembalian</h2>
        <span class="detail-id">#{{ $pengembalian->id }}</span>
    </div>

    <div class="detail-body">
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">ID Peminjaman</span>
                <span class="detail-value">{{ $pengembalian->pinjam_id }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Anggota</span>
                <span class="detail-value">{{ $pengembalian->anggota->nama }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">ID Anggota</span>
                <span class="detail-value">{{ $pengembalian->anggota->id_anggota }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Buku</span>
                <span class="detail-value">{{ $pengembalian->book->judul }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Tanggal Pinjam</span>
                <span class="detail-value">{{ $pengembalian->tanggal_pinjam->format('d-m-Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Kembali (Seharusnya)</span>
                <span class="detail-value">{{ $pengembalian->tanggal_kembali->format('d-m-Y') }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Tanggal Dikembalikan</span>
                <span class="detail-value highlight">{{ $pengembalian->tanggal_dikembalikan->format('d-m-Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Denda</span>
                @if($pengembalian->denda > 0)
                    <span class="detail-value denda-text">Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}</span>
                @else
                    <span class="detail-value no-denda-text">Tidak Ada Denda</span>
                @endif
            </div>
        </div>

        @if($pengembalian->denda > 0)
            <div class="denda-info">
                <p>⚠️ Terlambat {{ \Carbon\Carbon::parse($pengembalian->tanggal_dikembalikan)->diffInDays($pengembalian->tanggal_kembali) }} hari @ Rp 5.000/hari</p>
            </div>
        @endif
    </div>

    <div class="detail-actions">
        <a href="{{ route('pengembalian.index') }}" class="btn-back">Kembali ke Daftar</a>
    </div>
</div>
@endsection

