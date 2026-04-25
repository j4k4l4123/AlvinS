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
        Welcome back, <strong>{{ $name }}</strong> 👋
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
                    <h3 class="item-title">1,248</h3>
                    <p class="item-detail">Total Books</p>
                </div>
            </div>
        </div>

        <div class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">86</h3>
                    <p class="item-detail">Active Loans</p>
                </div>
            </div>
        </div>

        <div class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title">342</h3>
                    <p class="item-detail">Members</p>
                </div>
            </div>
        </div>

        <div class="item-card">
            <div class="tilt-layer">
                <div class="item-body">
                    <h3 class="item-title" style="color: var(--pu-danger);">12</h3>
                    <p class="item-detail">Overdue</p>
                </div>
            </div>
        </div>

    </div>

    <!-- RECENT ACTIVITY -->
    <div class="content-card" style="padding: 20px;">
        <h2 style="margin-bottom: 15px;">🔔 Recent Activity</h2>

        <div class="activity-item">
            <span class="activity-text">New borrowing — <strong>The Great Gatsby</strong></span>
            <span class="activity-time">2 min ago</span>
        </div>

        <div class="activity-item">
            <span class="activity-text">Returned — <strong>Clean Code</strong></span>
            <span class="activity-time">15 min ago</span>
        </div>

        <div class="activity-item">
            <span class="activity-text">New member — <strong>Rina</strong></span>
            <span class="activity-time">1 hour ago</span>
        </div>

    </div>

</div>

@endsection