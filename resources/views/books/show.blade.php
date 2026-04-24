@extends('layouts.app')

@section('content')
<div class="detail-container">
    <div class="detail-header">
        <div class="detail-icon">📚</div>
        <h2>Detail Buku</h2>
        <span class="detail-id">{{ $book->id_buku }}</span>
    </div>

    <div class="detail-body">
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Judul</span>
                <span class="detail-value">{{ $book->judul }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Kategori</span>
                <span class="detail-value">{{ $book->kategori }}</span>
            </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Pengarang</span>
                <span class="detail-value">{{ $book->pengarang }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Penerbit</span>
                <span class="detail-value">{{ $book->penerbit }}</span>
            </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Tahun Terbit</span>
                <span class="detail-value">{{ $book->thn_terbit }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value">{{ $book->created_at->format('d-m-Y') }}</span>
            </div>

        <div class="detail-row full">
            <div class="detail-item">
                <span class="detail-label">Keterangan</span>
                <span class="detail-value">{{ $book->keterangan ?? 'Tidak ada keterangan' }}</span>
            </div>
    </div>

    <div class="detail-actions">
        <a href="{{ route('books.index') }}" class="btn-back">← Kembali</a>
        <a href="{{ route('books.edit', $book->id) }}" class="btn-edit">✏️ Edit</a>
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
    .detail-row.full {
        grid-template-columns: 1fr;
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
    .detail-actions {
        padding: 20px 30px;
        background: #f0fdf4;
        border-top: 2px dashed #86efac;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
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
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 14px 28px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        transition: all 0.3s;
    }
    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
    }
</style>
@endsection
