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
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Notifikasi</h3><p class="item-detail">{{ $notificationsCount }} notifikasi belum dibaca</p></div></div>
    </a>
    <a href="{{ route('member.fines') }}" class="item-card">
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Denda</h3><p class="item-detail">Total belum lunas Rp {{ number_format($totalFines, 0, ',', '.') }}</p></div></div>
    </a>
    <a href="{{ route('member.cancel-membership') }}" class="item-card">
        <div class="tilt-layer"><div class="item-body"><h3 class="item-title">Batalkan Keanggotaan</h3><p class="item-detail">Ajukan pembatalan ke pustakawan</p></div></div>
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
        <div class="alert-error" style="margin-top: 16px;">
            Permintaan pembatalan keanggotaan sedang diproses.
        </div>
    @endif
</div>

<div class="content-card" style="padding: 20px; margin-bottom: 24px;">
    <h2 style="margin-bottom: 16px;">Peminjaman Aktif</h2>
    @if(method_exists($activeBorrowings, 'count') && $activeBorrowings->count() > 0)
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeBorrowings as $borrowing)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $borrowing->book?->judul ?? '-' }}</td>
                            <td>{{ $borrowing->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $borrowing->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($borrowing->isOverdue())
                                    <span class="status-badge status-borrowed">Overdue ({{ $borrowing->daysOverdue() }} hari)</span>
                                @else
                                    <span class="status-badge status-available">On Time</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($activeBorrowings, 'links'))
            <div style="margin-top: 16px;">{{ $activeBorrowings->links() }}</div>
        @endif
    @else
        <p class="text-muted">Belum ada peminjaman aktif.</p>
    @endif
</div>

<div class="content-card" style="padding: 20px;">
    <h2 style="margin-bottom: 16px;">Riwayat Peminjaman</h2>
    @if(method_exists($borrowingHistory, 'count') && $borrowingHistory->count() > 0)
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Buku</th>
                        <th>Dipinjam</th>
                        <th>Dikembalikan</th>
                        <th>Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowingHistory as $history)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $history->book?->judul ?? '-' }}</td>
                            <td>{{ $history->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $history->pengembalian?->tanggal_dikembalikan?->format('d/m/Y') ?? 'Belum kembali' }}</td>
                            <td>
                                @if(($history->fine?->amount ?? 0) > 0 && $history->fine?->status === 'unpaid')
                                    <span style="color: #dc2626; font-weight: 700;">Rp {{ number_format($history->fine->amount, 0, ',', '.') }}</span>
                                @else
                                    <span style="color: #16a34a;">Rp 0</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($borrowingHistory, 'links'))
            <div style="margin-top: 16px;">{{ $borrowingHistory->links() }}</div>
        @endif
    @else
        <p class="text-muted">Belum ada riwayat peminjaman.</p>
    @endif
</div>
@endsection
