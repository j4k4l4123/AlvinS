@extends('layouts.app')

@section('content')
<div class="detail-container">
    <div class="detail-header">
        <div class="detail-icon"></div>
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
                    <span class="detail-value denda">Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}</span>
                @else
                    <span class="detail-value no-denda">Tidak Ada Denda</span>
                @endif
            </div>
        </div>

        @if($pengembalian->denda > 0)
            <div class="denda-info">
                <p>? Terlambat {{ \Carbon\Carbon::parse($pengembalian->tanggal_dikembalikan)->diffInDays($pengembalian->tanggal_kembali) }} hari @ Rp 5.000/hari</p>
            </div>
        @endif
    </div>

    <div class="detail-actions">
        <a href="{{ route('pengembalian.index') }}" class="btn-back">Kembali ke Daftar</a>
    </div>
</div>

<style>
    .detail-container {
        max-width: 700px;
        margin: 0 auto;
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 2px solid #dcfce7;
    }
    .detail-header {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        padding: 30px;
        text-align: center;
        color: white;
    }
    .detail-icon {
        font-size: 50px;
        margin-bottom: 10px;
    }
    .detail-header h2 {
        margin: 0;
        font-size: 1.5rem;
    }
    .detail-id {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 5px 15px;
        border-radius: 20px;
        margin-top: 10px;
        font-weight: 600;
    }
    .detail-body {
        padding: 30px;
    }
    .detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #dcfce7;
    }
    .detail-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    .detail-label {
        color: #6b7280;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .detail-value {
        color: #15803d;
        font-size: 16px;
        font-weight: 600;
    }
    .detail-value.highlight {
        color: #22c55e;
        font-size: 18px;
    }
    .detail-value.denda {
        color: #dc2626;
        font-size: 20px;
    }
    .detail-value.no-denda {
        color: #22c55e;
    }
    .denda-info {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
        color: #991b1b;
    }
    .detail-actions {
        padding: 20px 30px;
        background: #f0fdf4;
        border-top: 2px dashed #86efac;
    }
    .btn-back {
        display: inline-block;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 14px 28px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
    }
</style>
@endsection
