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
                <span class="detail-label">ISBN</span>
                <span class="detail-value">{{ $book->isbn ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Barcode</span>
                <span class="detail-value">{{ $book->barcode ?? $book->id_buku }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Tahun Terbit</span>
                <span class="detail-value">{{ $book->thn_terbit }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status Copy</span>
                <span class="detail-value">
                    @if($book->copy_status === 'available')
                        <span class="status-badge status-available">✅ {{ $book->copyStatusLabel() }}</span>
                    @elseif($book->copy_status === 'reserved')
                        <span class="status-badge" style="background:#fef3c7; color:#92400e;">📌 {{ $book->copyStatusLabel() }}</span>
                    @else
                        <span class="status-badge status-borrowed">⏳ {{ $book->copyStatusLabel() }}</span>
                    @endif
                </span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Bahasa</span>
                <span class="detail-value">{{ $book->language ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Subjek</span>
                <span class="detail-value">{{ $book->subject ?? '-' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Jumlah Halaman</span>
                <span class="detail-value">{{ $book->number_of_pages ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Format</span>
                <span class="detail-value">{{ $book->format ?? '-' }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Rak</span>
                <span class="detail-value">{{ $book->rack?->name ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Harga Buku</span>
                <span class="detail-value">Rp {{ number_format((float) ($book->price ?? 0), 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Denda per Hari</span>
                <span class="detail-value">Rp {{ number_format((float) ($book->daily_late_fee ?? 0), 0, ',', '.') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value">{{ $book->created_at->format('d-m-Y') }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Prefix Kode Copy</span>
                <span class="detail-value">{{ $book->copy_code_prefix ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Kondisi Copy</span>
                <span class="detail-value">{{ ucfirst($book->copy_condition ?? 'good') }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Maks Lama Pinjam</span>
                <span class="detail-value">{{ $book->max_loan_days ?? 14 }} hari</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Maks Perpanjangan</span>
                <span class="detail-value">{{ $book->max_renewals ?? 1 }}x</span>
            </div>
        </div>

        <div class="detail-row full">
            <div class="detail-item">
                <span class="detail-label">Keterangan</span>
                <span class="detail-value">{{ $book->keterangan ?? 'Tidak ada keterangan' }}</span>
            </div>
        </div>
    </div>

    @php($isMemberView = request()->routeIs('member.books.show'))
    <div class="detail-actions">
        <a href="{{ $isMemberView ? route('member.books.index') : route('books.index') }}" class="btn-back">← Kembali</a>
        @unless($isMemberView)
            <a href="{{ route('books.edit', $book->id) }}" class="btn-action btn-edit">✏️ Edit</a>
            <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Hapus buku ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-delete">🗑️ Hapus</button>
            </form>
        @endunless
    </div>
</div>
@endsection

