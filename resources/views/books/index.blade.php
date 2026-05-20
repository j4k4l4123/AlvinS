@extends('layouts.app')

@section('content')
@php($isMemberView = request()->routeIs('member.*'))
<div class="page-header">
    <h1>📚 Daftar Buku</h1>
    @unless($isMemberView)
        <a href="{{ route('books.create') }}" class="btn-add"><span class="icon">+</span> Tambah Buku</a>
    @endunless
</div>

<div class="search-filter-box">
    <form method="GET" action="{{ $isMemberView ? route('member.books.index') : route('books.index') }}" class="search-form">
        <input type="text" name="search" placeholder="🔍 Cari judul, pengarang, kategori, subjek..." value="{{ request('search') }}" class="search-input">

        <input type="text" name="author" placeholder="Pengarang" value="{{ request('author') }}" class="search-input">
        <input type="text" name="subject" placeholder="Subjek" value="{{ request('subject') }}" class="search-input">

        <select name="kategori" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach(['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Biografi', 'Lainnya'] as $kat)
                <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
            @endforeach
        </select>

        <select name="availability" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Tersedia</option>
            <option value="reference_only" {{ request('availability') == 'reference_only' ? 'selected' : '' }}>Reference Only</option>
        </select>

        <button type="submit" class="btn-search">Cari</button>
        @if(request('search') || request('kategori') || request('author') || request('subject') || request('availability'))
            <a href="{{ $isMemberView ? route('member.books.index') : route('books.index') }}" class="btn-reset">Reset</a>
        @endif
    </form>
</div>

@if(session('success'))
    <div class="alert-success">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if((request('search') || request('kategori') || request('author') || request('subject') || request('availability')) && $books->count() == 0)
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Hasil tidak ada</h3>
        <p class="text-muted">Tidak ditemukan buku dengan kata kunci "{{ request('search') }}"</p>
    </div>
@elseif($books->count() > 0)
    <div class="items-grid">
        @foreach($books as $book)
            <div class="item-card">
                @if($isMemberView)
                    <a href="{{ route('member.books.show', $book->id) }}" style="text-decoration: none; color: inherit; display:block;">
                @else
                    <a href="{{ route('books.show', $book->id) }}" style="text-decoration: none; color: inherit; display:block;">
                @endif
                    <div class="tilt-layer">
                        <div class="item-header">
                            <span class="status-badge">{{ $book->kategori }}</span>
                            <span class="item-id">#{{ $book->id_buku }}</span>
                        </div>
                        @php($activeReservation = $book->activeReservation())
                        <div class="item-status-row" style="padding: 10px 22px 0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            @if($book->reference_only)
                                <span class="status-badge status-borrowed">📘 Reference Only</span>
                            @elseif($book->copy_status === 'available')
                                <span class="status-badge status-available">✅ {{ $book->copyStatusLabel() }}</span>
                            @elseif($book->copy_status === 'reserved')
                                <span class="status-badge" style="background:#fef3c7; color:#92400e;">📌 {{ $book->copyStatusLabel() }}</span>
                            @else
                                <span class="status-badge status-borrowed">⏳ {{ $book->copyStatusLabel() }}</span>
                            @endif

                            @unless($isMemberView)
                                @if($activeReservation)
                                    <span class="status-badge" style="background:#fef3c7; color:#92400e;">📌 {{ $activeReservation->status === 'approved' ? 'Reservasi Disetujui' : 'Menunggu Approval Reservasi' }}</span>
                                @endif
                            @endunless
                        </div>
                        <div class="item-body">
                            <h3 class="item-title">{{ $book->judul }}</h3>
                            <p class="item-detail">✍️ {{ $book->pengarang }}</p>
                            <p class="item-detail">🏢 {{ $book->penerbit }}, {{ $book->thn_terbit }}</p>
                            @unless($isMemberView)
                                <p class="item-detail">🗂️ Rak: {{ $book->rack?->name ?? '-' }} | 📦 Stok: {{ $book->stock ?? 0 }}</p>
                                <p class="item-detail">🆔 Prefix Copy: {{ $book->copy_code_prefix ?? '-' }} | 🛠️ Kondisi: {{ ucfirst($book->copy_condition ?? 'good') }}</p>
                                <p class="item-detail">💳 Harga: Rp {{ number_format((float) ($book->price ?? 0), 0, ',', '.') }} | ⏱️ Denda/hari: Rp {{ number_format((float) ($book->daily_late_fee ?? 0), 0, ',', '.') }}</p>
                                <p class="item-detail">📅 Maks pinjam: {{ $book->max_loan_days ?? 14 }} hari | 🔄 Maks perpanjangan: {{ $book->max_renewals ?? 1 }}x</p>
                            @endunless
                            @if($book->keterangan)
                                <p class="item-desc">{{ Str::limit($book->keterangan, 60) }}</p>
                            @endif
                        </div>
                    </div>
                </a>

                @if($isMemberView && $book->isReservable())
                    <div style="padding: 0 22px 22px;">
                        <form method="POST" action="{{ route('member.books.reserve', $book) }}" style="margin-top:12px;">
                            @csrf
                            <button type="submit" class="btn-member-borrow">Reservasi</button>
                        </form>
                    </div>
                @elseif($isMemberView && $book->reference_only)
                    <div style="padding: 0 22px 22px; color:#b91c1c; font-weight:600; font-size:13px;">Reference only, tidak bisa dipinjam.</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="pagination-info">
        <span class="text-muted">Showing {{ $books->count() }} books</span>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3>Belum ada buku</h3>
        <p class="text-muted">Tambahkan buku pertama ke perpustakaan!</p>
        <a href="{{ route('books.create') }}" class="btn-add"><span class="icon">+</span> Tambah Buku</a>
    </div>
@endif

<div class="bottom-spacer"></div>
@endsection

