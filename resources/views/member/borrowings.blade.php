@extends('layouts.app')

@section('title', 'Peminjaman Saya - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Peminjaman Saya</h1>
    <p class="text-muted">Kelola buku yang sedang kamu pinjam.</p>
</div>

@if(session('success'))
    <div class="alert-success" style="margin-bottom: 16px;">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-error" style="margin-bottom: 16px;">
        <span class="alert-icon">⚠️</span> {{ session('error') }}
    </div>
@endif

<div class="content-card" style="padding: 20px; margin-bottom:24px;">
    <h2 style="margin-bottom: 16px;">Scan Kartu & Barcode Buku</h2>
    <p class="text-muted" style="margin-bottom:16px;">Gunakan nomor kartu perpustakaan dan barcode/ID buku untuk membuat reservasi. Reservasi aktif selama 1 hari.</p>

    <form method="POST" action="{{ route('member.borrowings.store') }}" class="styled-form">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label for="card_number">Nomor Kartu / Barcode Kartu</label>
                <input type="text" id="card_number" name="card_number" class="form-input" value="{{ old('card_number', $libraryCard?->card_number) }}" required>
            </div>
            <div class="form-group">
                <label for="book_barcode">Barcode / ID Buku</label>
                <input type="text" id="book_barcode" name="book_barcode" class="form-input" value="{{ old('book_barcode') }}" placeholder="Contoh: BKU001" required>
            </div>
        </div>
        <div class="form-actions" style="justify-content:flex-start;">
            <button type="submit" class="btn-submit">Scan & Reservasi Buku</button>
        </div>
    </form>
</div>

<div class="content-card" style="padding: 20px; margin-bottom:24px;">
    <h2 style="margin-bottom: 16px;">Reservasi Aktif</h2>
    <p class="text-muted" style="margin-bottom:16px;">Reservasi menunggu persetujuan librarian dan otomatis hangus setelah 1 hari jika belum diproses.</p>

    @if($activeReservations->count() > 0)
        <div style="display:grid; gap:14px;">
            @foreach($activeReservations as $reservation)
                <div style="border:1px solid rgba(250, 204, 21, 0.25); border-radius:18px; padding:18px; background:rgba(255,255,255,0.62);">
                    <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                        <div>
                            <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $reservation->book?->judul ?? '-' }}</h3>
                            <div class="text-muted">Barcode Buku: <strong>{{ $reservation->book?->id_buku ?? '-' }}</strong></div>
                        </div>
                        <span class="status-badge" style="background:#fef3c7; color:#92400e;">{{ $reservation->status === 'approved' ? 'Disetujui' : 'Menunggu Approval' }} sampai {{ $reservation->expires_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted">Belum ada reservasi aktif.</p>
    @endif
</div>

<div class="content-card" style="padding: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
        <div>
            <h2 style="margin:0;">Daftar Peminjaman Aktif</h2>
            <p class="text-muted" style="margin:6px 0 0;">Lihat status buku, jatuh tempo, dan ajukan perpanjangan ke librarian.</p>
        </div>
        <div class="status-badge status-available">{{ method_exists($activeBorrowings, 'total') ? $activeBorrowings->total() : $activeBorrowings->count() }} aktif</div>
    </div>

    @if(method_exists($activeBorrowings, 'count') && $activeBorrowings->count() > 0)
        <div style="display:grid; gap:16px;">
            @foreach($activeBorrowings as $borrowing)
                <div style="border:1px solid rgba(52, 211, 153, 0.18); border-radius:18px; padding:18px; background:rgba(255,255,255,0.62);">
                    <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                        <div>
                            <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $borrowing->book?->judul ?? '-' }}</h3>
                            <div class="text-muted">Barcode Buku: <strong>{{ $borrowing->book?->id_buku ?? '-' }}</strong></div>
                        </div>
                        <div>
                            @if($borrowing->isOverdue())
                                <span class="status-badge status-borrowed">Overdue {{ $borrowing->daysOverdue() }} hari</span>
                            @else
                                <span class="status-badge status-available">Aktif</span>
                            @endif
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-top:16px;">
                        <div style="padding:12px 14px; border-radius:14px; background:rgba(236,253,245,0.7);">
                            <div class="text-muted">Tanggal Pinjam</div>
                            <strong>{{ $borrowing->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</strong>
                        </div>
                        <div style="padding:12px 14px; border-radius:14px; background:rgba(236,253,245,0.7);">
                            <div class="text-muted">Jatuh Tempo</div>
                            <strong>{{ $borrowing->tanggal_kembali?->format('d/m/Y') ?? '-' }}</strong>
                        </div>
                        <div style="padding:12px 14px; border-radius:14px; background:rgba(236,253,245,0.7);">
                            <div class="text-muted">Perpanjangan</div>
                            <strong>{{ $pendingRenewals->has((string) $borrowing->id) ? 'Menunggu approval librarian' : 'Belum diajukan' }}</strong>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px;">
                        @if(! $pendingRenewals->has((string) $borrowing->id))
                            <form method="POST" action="{{ route('member.borrowings.renew', $borrowing) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-action btn-edit">Ajukan Perpanjangan</button>
                            </form>
                        @else
                            <button type="button" class="btn-action" style="background:#94a3b8; color:white;">Menunggu Approval</button>
                        @endif
                        <form method="POST" action="{{ route('member.borrowings.return', $borrowing) }}">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn-return">Kembalikan</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if(method_exists($activeBorrowings, 'links'))
            <div style="margin-top: 16px;">{{ $activeBorrowings->links() }}</div>
        @endif
    @else
        <p class="text-muted">Belum ada peminjaman aktif.</p>
    @endif
</div>
@endsection
