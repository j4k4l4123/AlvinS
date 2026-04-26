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
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Tahun Terbit</span>
                <span class="detail-value">{{ $book->thn_terbit }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    @if($book->isAvailable())
                        <span class="status-badge status-available">✅ Tersedia</span>
                    @else
                        <span class="status-badge status-borrowed">⏳ Dipinjam</span>
                    @endif
                </span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value">{{ $book->created_at->format('d-m-Y') }}</span>
            </div>
        </div>

        <div class="detail-row full">
            <div class="detail-item">
                <span class="detail-label">Keterangan</span>
                <span class="detail-value">{{ $book->keterangan ?? 'Tidak ada keterangan' }}</span>
            </div>
        </div>
    </div>

    <div class="detail-actions">
        <a href="{{ route('books.index') }}" class="btn-back">← Kembali</a>
        <a href="{{ route('books.edit', $book->id) }}" class="btn-action btn-edit">✏️ Edit</a>
    </div>
</div>
@endsection

