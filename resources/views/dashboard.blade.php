@extends('layouts.app')

@section('title', 'Dashboard — PerpusKu')
@section('body-class', 'dashboard-page')

@section('content')

<div class="content-card">

    <!-- HEADER -->
    <div class="page-header">
        <h1>Dashboard</h1>
    </div>

    <!-- GREETING -->
    <p class="text-muted" style="margin-bottom: 20px;">
        Selamat datang kembali, <strong>{{ $name }}</strong> 👋
    </p>

    <!-- QUICK MENU -->
    <div class="items-grid" style="margin-bottom: 30px;">
        <a href="{{ route('pinjam.index') }}" class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">📚 Peminjaman</h3>
                    <p class="item-detail">Manage borrowings</p>
                </div>
            </div>
        </a>

        <a href="{{ route('pengembalian.index') }}" class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">📥 Pengembalian</h3>
                    <p class="item-detail">Process returns</p>
                </div>
            </div>
        </a>

        <a href="{{ route('anggota.index') }}" class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">👤 Anggota</h3>
                    <p class="item-detail">Library members</p>
                </div>
            </div>
        </a>

        <a href="{{ route('books.index') }}" class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">📖 Buku</h3>
                    <p class="item-detail">Book catalog</p>
                </div>
            </div>
        </a>
    </div>

    <!-- STATS -->
    <div class="items-grid" style="margin-bottom: 30px;">

        <div class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">{{ number_format($totalBooks) }}</h3>
                    <p class="item-detail">Total Buku</p>
                </div>
            </div>
        </div>

        <div class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">{{ number_format($activeLoans) }}</h3>
                    <p class="item-detail">Sedang Dipinjam</p>
                </div>
            </div>
        </div>

        <div class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">{{ number_format($members) }}</h3>
                    <p class="item-detail">Anggota</p>
                </div>
            </div>
        </div>

        <div class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title" style="color: var(--pu-danger);">{{ number_format($overdue) }}</h3>
                    <p class="item-detail">Terlambat</p>
                </div>
            </div>
        </div>

    </div>

    <!-- RECENT ACTIVITY -->
    <div class="content-card" style="padding: 20px;">
        <h2 style="margin-bottom: 15px;">🔔 Aktivitas Terbaru</h2>

        @if($recentActivity->count() > 0)
            @foreach($recentActivity as $activity)
                <div class="activity-item">
                    <span class="activity-text">{!! $activity['text'] !!}</span>
                    <span class="activity-time">{{ $activity['time']->diffForHumans() }}</span>
                </div>
            @endforeach
        @else
            <div class="activity-item">
                <span class="activity-text">Belum ada aktivitas.</span>
            </div>
        @endif

    </div>

</div>

@endsection