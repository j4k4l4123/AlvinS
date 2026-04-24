@extends('layouts.app')

@section('content')
<div class="detail-container">
    <div class="detail-header">
        <div class="detail-icon">📖</div>
        <h2>Detail Peminjaman</h2>
        <span class="detail-id">#{{ $pinjam->id }}</span>
    </div>

    <div class="detail-body">
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Nama Anggota</span>
                <span class="detail-value">{{ $pinjam->anggota->nama }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">ID Anggota</span>
                <span class="detail-value">{{ $pinjam->anggota->id_anggota }}</span>
            </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Judul Buku</span>
                <span class="detail-value">{{ $pinjam->book->judul }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">ID Buku</span>
                <span class="detail-value">{{ $pinjam->book->id_buku }}</span>
            </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Tanggal Pinjam</span>
                <span class="detail-value">{{ $pinjam->tanggal_pinjam->format('d-m-Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Kembali</span>
                <span class="detail-value">{{ $pinjam->tanggal_kembali->format('d-m-Y') }}</span>
            </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Status</span>
                @if($pinjam->status == 'dipinjam')
                    <span class="detail-value status-borrowed">⏳ Dipinjam</span>
                @else
                    <span class="detail-value status-returned">✅ Dikembalikan</span>
                @endif
            </div>
            <div class="detail-item">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value">{{ $pinjam->created_at->format('d-m-Y H:i') }}</span>
            </div>
    </div>

    <div class="detail-actions">
        <a href="{{ route('pinjam.index') }}" class="btn-back">← Kembali</a>
        <a href="{{ route('pinjam.edit', $pinjam->id) }}" class="btn-edit">✏️ Edit</a>
        @if($pinjam->status == 'dipinjam')
            <a href="{{ route('pengembalian.create') }}" class="btn-return">📥 Kembalikan</a>
        @endif
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
    .status-borrowed {
        color: #dc2626;
    }
    .status-returned {
        color: #22c55e;
    }
    .detail-actions {
        padding: 20px 30px;
        background: #f0fdf4;
        border-top: 2px dashed #86efac;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    .btn-back {
        display: inline-block;
        background: #e5e7eb;
        color: #374151;
        padding: 14px 28px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-back:hover {
        background: #9ca3af;
        color: white;
    }
    .btn-edit {
        display: inline-block;
        background: #dbeafe;
        color: #1d4ed8;
        padding: 14px 28px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-edit:hover {
        background: #1d4ed8;
        color: white;
    }
    .btn-return {
        display: inline-block;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 14px 28px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        transition: all 0.3s;
    }
    .btn-return:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
    }
</style>
@endsection
