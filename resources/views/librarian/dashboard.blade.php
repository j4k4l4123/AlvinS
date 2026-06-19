@extends('layouts.app')

@section('title', 'Librarian Dashboard - PerpusKu')
@section('body-class', 'dashboard-page')

@section('content')
<div class="page-header">
    <h1>Librarian Dashboard</h1>
    <p class="text-muted">Manage your library operations</p>
</div>

<div class="items-grid" style="margin-bottom: 30px;">
    <a href="{{ route('books.index') }}" class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">Books</h3>
                <p class="item-detail">Manage book catalog</p>
            </div>
        </div>
    </a>

    <a href="{{ route('anggota.index') }}" class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">Members</h3>
                <p class="item-detail">Manage members</p>
            </div>
        </div>
    </a>

    <a href="{{ route('pinjam.index') }}" class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">Borrowings</h3>
                <p class="item-detail">Track loans</p>
            </div>
        </div>
    </a>

    <a href="{{ route('pengembalian.index') }}" class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">Returns</h3>
                <p class="item-detail">Process returns</p>
            </div>
        </div>
    </a>

    <a href="{{ route('library-cards.index') }}" class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">Library Cards</h3>
                <p class="item-detail">Manage cards</p>
            </div>
        </div>
    </a>

    <a href="{{ route('membership-requests.index') }}" class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">Membership Requests</h3>
                <p class="item-detail">Review requests</p>
            </div>
        </div>
    </a>

</div>

<div class="items-grid" style="margin-bottom: 30px;">
    <div class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">{{ number_format($totalBooks) }}</h3>
                <p class="item-detail">Total Books</p>
            </div>
        </div>
    </div>

    <div class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">{{ number_format($activeLoans) }}</h3>
                <p class="item-detail">Active Loans</p>
            </div>
        </div>
    </div>

    <div class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title">{{ number_format($members) }}</h3>
                <p class="item-detail">Total Members</p>
            </div>
        </div>
    </div>

    <div class="item-card">
        <div class="tilt-layer">
            <div class="item-body">
                <h3 class="item-title" style="color: var(--pu-danger);">{{ number_format($overdue) }}</h3>
                <p class="item-detail">Overdue</p>
            </div>
        </div>
    </div>
</div>

@if($overdueBorrowings->count() > 0)
<div class="content-card" style="padding: 20px; margin-bottom: 20px;">
    <h2 style="margin-bottom: 15px;">Overdue Borrowings</h2>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Borrow ID</th>
                    <th>Book</th>
                    <th>Member</th>
                    <th>Due Date</th>
                    <th>Days Late</th>
                    <th>Fine</th>
                </tr>
            </thead>
            <tbody>
                @foreach($overdueBorrowings as $borrowing)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>#{{ $borrowing->id }}</td>
                        <td>{{ $borrowing->book?->judul ?? '-' }}</td>
                        <td>{{ $borrowing->anggota?->nama ?? '-' }}</td>
                        <td>{{ $borrowing->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
                        <td style="color: var(--pu-danger); font-weight: bold;">{{ $borrowing->daysOverdue() }} days</td>
                        <td>Rp {{ number_format($borrowing->calculateFine(), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="content-card" style="padding: 20px;">
    <h2 style="margin-bottom: 15px;">Recent Activity</h2>

    @if($recentActivity->count() > 0)
        @foreach($recentActivity as $activity)
            <div class="activity-item">
                <span class="activity-text">{!! $activity['text'] !!}</span>
                <span class="activity-time">{{ $activity['time']->diffForHumans() }}</span>
            </div>
        @endforeach
    @else
        <div class="activity-item">
            <span class="activity-text">No activity yet.</span>
        </div>
    @endif
</div>
@endsection
