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

<div class="content-card" style="padding: 20px;">
    <h2 style="margin-bottom: 16px;">Daftar Peminjaman Aktif</h2>

    @if(method_exists($activeBorrowings, 'count') && $activeBorrowings->count() > 0)
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeBorrowings as $borrowing)
                        <tr>
                            <td>{{ $borrowing->book?->judul ?? '-' }}</td>
                            <td>{{ $borrowing->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $borrowing->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($borrowing->isOverdue())
                                    <span class="status-badge status-borrowed">Overdue</span>
                                @else
                                    <span class="status-badge status-available">Aktif</span>
                                @endif
                            </td>
                            <td style="display:flex; gap:8px; flex-wrap:wrap;">
                                <form method="POST" action="{{ route('member.borrowings.renew', $borrowing) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-action">Perpanjang</button>
                                </form>
                                <form method="POST" action="{{ route('member.borrowings.return', $borrowing) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-return">Kembalikan</button>
                                </form>
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
@endsection
