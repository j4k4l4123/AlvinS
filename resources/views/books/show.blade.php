@extends('layouts.app')

@section('content')
@php($isMemberView = request()->routeIs('member.books.show'))

<div class="detail-container" style="display:grid; gap:20px;">
    <div class="content-card" style="padding:28px;">
        <div style="display:flex; justify-content:space-between; gap:18px; flex-wrap:wrap; align-items:flex-start; margin-bottom:24px;">
            <div style="max-width:760px;">
                <div class="text-muted" style="margin-bottom:10px;">ID Buku</div>
                <div class="status-badge">{{ $book->id_buku }}</div>
                <h1 style="margin:16px 0 10px; color:var(--pu-forest); line-height:1.2;">{{ $book->judul }}</h1>
                <p class="text-muted" style="margin:0; line-height:1.6;">Detail lengkap buku untuk pengelolaan katalog dan inventaris.</p>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px; align-items:flex-end;">
                @if($book->rack?->name)
                    <div style="padding:10px 14px; border-radius:14px; background:rgba(236,253,245,0.8); text-align:right;">
                        <div class="text-muted" style="margin-bottom:4px;">Rak Buku</div>
                        <strong>{{ $book->rack->name }}</strong>
                        @if($book->rack?->location_note)
                            <div class="text-muted" style="margin-top:4px; max-width:220px;">{{ $book->rack->location_note }}</div>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px;">
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Pengarang</div>
                <strong>{{ $book->pengarang ?? '-' }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Penerbit</div>
                <strong>{{ $book->penerbit ?? '-' }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Kategori</div>
                <strong>{{ $book->kategori ?? '-' }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Barcode</div>
                <strong>{{ $book->barcode ?? $book->id_buku }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">ISBN</div>
                <strong>{{ $book->isbn ?? '-' }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Tahun Terbit</div>
                <strong>{{ $book->thn_terbit ?? '-' }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Bahasa</div>
                <strong>{{ $book->language ?? '-' }}</strong>
            </div>

            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Jumlah Halaman</div>
                <strong>{{ $book->number_of_pages ?? '-' }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Format</div>
                <strong>{{ $book->format ?? '-' }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Maks Lama Pinjam</div>
                <strong>{{ $book->max_loan_days ?? 14 }} hari</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Maks Perpanjangan</div>
                <strong>{{ $book->max_renewals ?? 1 }}x</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Harga Buku</div>
                <strong>Rp {{ number_format((float) ($book->price ?? 0), 0, ',', '.') }}</strong>
            </div>
            <div style="padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
                <div class="text-muted">Denda per Hari</div>
                <strong>Rp {{ number_format((float) ($book->daily_late_fee ?? 0), 0, ',', '.') }}</strong>
            </div>
        </div>

        <div style="margin-top:18px; padding:18px; border-radius:16px; background:rgba(255,255,255,0.68); border:1px solid rgba(52,211,153,0.18);">
            <div class="text-muted" style="margin-bottom:8px;">Keterangan</div>
            <div style="line-height:1.6;">{{ $book->keterangan ?? 'Tidak ada keterangan.' }}</div>
        </div>
    </div>

    <div style="display:flex; gap:12px; flex-wrap:wrap;">
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
