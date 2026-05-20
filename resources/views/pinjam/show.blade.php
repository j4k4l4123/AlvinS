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
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Kode Copy</span>
                <span class="detail-value">{{ $pinjam->copy_code ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Jumlah Perpanjangan</span>
                <span class="detail-value">{{ $pinjam->renewal_count ?? 0 }}x</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Status</span>
                @if($pinjam->status == 'dipinjam')
                    <span class="detail-value status-borrowed">⏳ Dipinjam</span>
                @elseif($pinjam->status == 'hilang')
                    <span class="detail-value status-borrowed">🚨 Hilang</span>
                @elseif($pinjam->status == 'rusak')
                    <span class="detail-value status-borrowed">🛠️ Rusak</span>
                @else
                    <span class="detail-value status-returned">✅ Dikembalikan</span>
                @endif
            </div>
            <div class="detail-item">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value">{{ $pinjam->created_at->format('d-m-Y H:i') }}</span>
            </div>
        </div>
    </div>

    <div class="detail-actions">
        <a href="{{ route('pinjam.index') }}" class="btn-back">← Kembali</a>
        <a href="{{ route('pinjam.edit', $pinjam->id) }}" class="btn-action btn-edit">✏️ Edit</a>
        <form action="{{ route('pinjam.destroy', $pinjam->id) }}" method="POST" onsubmit="return confirm('Hapus data peminjaman ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-action btn-delete">🗑️ Hapus</button>
        </form>
        @if($pinjam->status == 'dipinjam')
            <a href="{{ route('pengembalian.create') }}" class="btn-return">📥 Kembalikan</a>
            <form action="{{ route('pinjam.lost', $pinjam->id) }}" method="POST" onsubmit="return confirm('Tandai buku ini sebagai hilang?');">
                @csrf
                @method('PUT')
                <button type="submit" class="btn-action btn-delete">🚨 Tandai Hilang</button>
            </form>
            <form action="{{ route('pinjam.damaged', $pinjam->id) }}" method="POST" onsubmit="return confirm('Tandai buku ini sebagai rusak?');">
                @csrf
                @method('PUT')
                <button type="submit" class="btn-action" style="background:#f59e0b; color:white;">🛠️ Tandai Rusak</button>
            </form>
        @endif
    </div>
</div>
@endsection

