@extends('layouts.app')

@section('title', 'Member Dashboard - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Member Dashboard</h1>
    <p class="text-muted">Selamat datang kembali, {{ auth()->user()->name }}</p>
</div>

@if(session('success'))
    <div class="alert-success" style="margin-bottom: 16px;">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if($profile)
<div class="content-card" style="padding: 20px; margin-bottom: 24px;">
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap; margin-bottom: 16px;">
        <h2>Profil Anggota</h2>
        <a href="{{ route('member.profile.edit') }}" class="btn-action">Edit Profil</a>
    </div>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
        <div><strong>ID Anggota:</strong> {{ $profile->id_anggota ?? '-' }}</div>
        <div><strong>Nama:</strong> {{ $profile->nama ?? '-' }}</div>
        <div><strong>Alamat:</strong> {{ $profile->alamat ?? '-' }}</div>
        <div><strong>No. Telepon:</strong> {{ $profile->no_tlp ?? '-' }}</div>
        <div><strong>Tanggal Daftar:</strong> {{ $profile->tanggal_daftar?->format('d/m/Y') ?? '-' }}</div>
        <div>
            <strong>Status:</strong>
            <span class="status-badge {{ ($profile->membership_status ?? 'active') === 'active' ? 'status-available' : 'status-borrowed' }}">
                {{ ucfirst(str_replace('_', ' ', $profile->membership_status ?? 'active')) }}
            </span>
        </div>
    </div>
</div>
@endif

<div class="items-grid" style="margin-bottom: 24px;">
    <a href="{{ route('member.books.index') }}" class="item-card">
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Cari Buku</h3><p class="item-detail">Telusuri katalog perpustakaan</p></div></div>
    </a>
    <a href="{{ route('member.library-card') }}" class="item-card">
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Kartu Perpustakaan</h3><p class="item-detail">Lihat kartu digital dan masa berlaku</p></div></div>
    </a>
    <a href="{{ route('member.borrowings.index') }}" class="item-card">
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Peminjaman Saya</h3><p class="item-detail">Perpanjang atau kembalikan buku</p></div></div>
    </a>
    <a href="{{ route('member.notifications') }}" class="item-card">
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Notifikasi</h3><p class="item-detail">Lihat semua pemberitahuan akunmu</p></div></div>
    </a>
    <a href="{{ route('member.fines') }}" class="item-card">
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Denda</h3><p class="item-detail">Total belum lunas Rp {{ number_format($totalFines, 0, ',', '.') }}</p></div></div>
    </a>
    <a href="{{ route('member.submissions') }}" class="item-card">
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Pengajuan Membership</h3><p class="item-detail">Ajukan perpanjangan atau pembatalan membership</p></div></div>
    </a>
</div>

<div class="content-card" style="padding: 20px; margin-bottom: 24px;">
    <h2 style="margin-bottom: 16px;">Ringkasan</h2>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
        <div><strong>Peminjaman aktif:</strong><br>{{ method_exists($activeBorrowings, 'total') ? $activeBorrowings->total() : $activeBorrowings->count() }}</div>
        <div><strong>Total riwayat:</strong><br>{{ method_exists($borrowingHistory, 'total') ? $borrowingHistory->total() : $borrowingHistory->count() }}</div>
        <div><strong>Total denda:</strong><br>Rp {{ number_format($totalFines, 0, ',', '.') }}</div>
        <div><strong>Status kartu:</strong><br>{{ $libraryCard?->status ? ucfirst($libraryCard->status) : 'Belum tersedia' }}</div>
    </div>

    @if($pendingCancellation)
        <div class="alert-error" style="margin-top: 16px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <span>Permintaan pembatalan keanggotaan sedang diproses.</span>
            <form method="POST" action="{{ route('membership-requests.cancel-own') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-edit">Batalkan Cancellation</button>
            </form>
        </div>
    @endif
</div>

<div class="content-card" style="padding: 24px; margin-bottom: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
        <div>
            <h2 style="margin:0;">Peminjaman Aktif</h2>
            <p class="text-muted" style="margin:6px 0 0;">Ringkasan buku yang sedang kamu pinjam sekarang.</p>
        </div>
        <a href="{{ route('member.borrowings.index') }}" class="btn-action btn-view">Kelola Peminjaman</a>
    </div>
    @if(method_exists($activeBorrowings, 'count') && $activeBorrowings->count() > 0)
        <div style="display:grid; gap:14px;">
            @foreach($activeBorrowings as $borrowing)
                <div style="border:1px solid rgba(52, 211, 153, 0.18); border-radius:18px; padding:18px; background:rgba(255,255,255,0.62);">
                    <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                        <div>
                            <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $borrowing->book?->judul ?? '-' }}</h3>
                            <div class="text-muted">Dipinjam: {{ $borrowing->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</div>
                            <div class="text-muted">Jatuh tempo: {{ $borrowing->tanggal_kembali?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div>
                            @if(\App\Models\Pinjam::isOverdue($borrowing))
                                <span class="status-badge status-borrowed">Overdue {{ \App\Models\Pinjam::daysOverdue($borrowing) }} hari</span>
                            @else
                                <span class="status-badge status-available">On Time</span>
                            @endif
                        </div>
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

<div class="content-card" style="padding: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
        <div>
            <h2 style="margin:0;">Riwayat Peminjaman</h2>
            <p class="text-muted" style="margin:6px 0 0;">Daftar histori peminjaman, pengembalian, dan status dendamu.</p>
        </div>
        <div class="status-badge">{{ method_exists($borrowingHistory, 'total') ? $borrowingHistory->total() : $borrowingHistory->count() }} riwayat</div>
    </div>
    @if(method_exists($borrowingHistory, 'count') && $borrowingHistory->count() > 0)
        <div style="display:grid; gap:14px;">
            @foreach($borrowingHistory as $history)
                <div style="border:1px solid rgba(52, 211, 153, 0.18); border-radius:18px; padding:18px; background:rgba(255,255,255,0.62);">
                    <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                        <div>
                            <h3 style="margin:0 0 8px; color:var(--pu-forest);">{{ $history->book?->judul ?? '-' }}</h3>
                            <div class="text-muted">Dipinjam: {{ $history->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</div>
                            <div class="text-muted">Dikembalikan: {{ $history->pengembalian?->tanggal_dikembalikan?->format('d/m/Y') ?? 'Belum kembali' }}</div>
                        </div>
                        <div style="text-align:right;">
                            @if(($history->fine?->amount ?? 0) > 0 && $history->fine?->status === 'unpaid')
                                <div style="color: #dc2626; font-weight: 700;">Rp {{ number_format($history->fine->amount, 0, ',', '.') }}</div>
                                <div class="text-muted">Denda belum lunas</div>
                            @else
                                <div style="color: #16a34a; font-weight: 700;">Rp 0</div>
                                <div class="text-muted">Tidak ada denda aktif</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if(method_exists($borrowingHistory, 'links'))
            <div style="margin-top: 16px;">{{ $borrowingHistory->links() }}</div>
        @endif
    @else
        <p class="text-muted">Belum ada riwayat peminjaman.</p>
    @endif
</div>
@endsection
