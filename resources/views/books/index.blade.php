@extends('layouts.app')

@section('content')
@php
    $isMemberView = request()->routeIs('member.*');
    $hasFilters = request('kategori') || request('availability') || request('release_sort') || request('from_year') || request('to_year');
@endphp
<div class="page-header">
    <h1>📚 Daftar Buku</h1>
    @unless($isMemberView)
        <a href="{{ route('books.create') }}" class="btn-add"><span class="icon">+</span> Tambah Buku</a>
    @endunless
</div>

<style>
    /* ===== FILTER TOGGLE BUTTON ===== */
    .btn-filter-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 20px;
        border: 1px solid rgba(52, 211, 153, 0.25);
        border-radius: var(--pu-radius-md);
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(8px);
        color: var(--pu-text);
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        position: relative;
    }

    .btn-filter-toggle:hover {
        background: rgba(255, 255, 255, 0.85);
        border-color: var(--pu-mint);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 78, 59, 0.08);
    }

    .btn-filter-toggle.active {
        background: rgba(52, 211, 153, 0.15);
        border-color: var(--pu-mint);
        color: var(--pu-forest);
        box-shadow: inset 0 2px 4px rgba(6, 78, 59, 0.05);
    }

    .btn-filter-toggle .filter-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        font-size: 10px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
    }

    .filter-toggle-icon {
        transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        display: inline-block;
    }

    .btn-filter-toggle.active .filter-toggle-icon {
        transform: rotate(180deg);
    }

    /* ===== FILTER PANEL ===== */
    .filter-panel {
        flex-basis: 100%;
        margin-top: 16px;
        background: rgba(255, 255, 255, 0.5);
        border: 1px solid rgba(52, 211, 153, 0.15);
        border-radius: var(--pu-radius-lg);
        padding: 0;
        backdrop-filter: blur(12px);
        box-shadow: 0 10px 30px rgba(6, 78, 59, 0.03);
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transition: max-height 0.4s cubic-bezier(0.22, 1, 0.36, 1),
                    opacity 0.3s ease,
                    padding 0.3s ease;
    }

    .filter-panel.show {
        max-height: 500px;
        opacity: 1;
        padding: 24px;
    }

    .filter-panel-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 11px;
        font-weight: 800;
        color: #064e3b;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .filter-group .filter-select,
    .filter-group .filter-input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid rgba(52, 211, 153, 0.25);
        border-radius: var(--pu-radius-md);
        font-size: 13px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        color: var(--pu-text);
        outline: none;
        transition: all 0.3s ease;
    }

    .filter-group .filter-select:focus,
    .filter-group .filter-input:focus {
        border-color: var(--pu-mint);
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.12);
    }

    .filter-panel-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid rgba(52, 211, 153, 0.1);
        flex-wrap: wrap;
    }

    .btn-apply-filter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 24px;
        background: linear-gradient(135deg, var(--pu-forest), #065f46);
        color: white;
        border: none;
        border-radius: var(--pu-radius-md);
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.3s ease;
    }

    .btn-apply-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(6, 78, 59, 0.3);
    }

    .btn-reset-filter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: transparent;
        color: var(--pu-text-soft);
        border: 1px solid rgba(52, 211, 153, 0.2);
        border-radius: var(--pu-radius-md);
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-reset-filter:hover {
        color: #b91c1c;
        border-color: #fca5a5;
        background: rgba(239, 68, 68, 0.05);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .filter-panel-content {
            grid-template-columns: 1fr;
        }

        .btn-filter-toggle {
            padding: 12px 16px;
            font-size: 13px;
        }

        .filter-panel.show {
            padding: 16px;
        }

        .filter-panel-actions {
            flex-direction: column;
        }

        .btn-apply-filter,
        .btn-reset-filter {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="search-filter-box">
    <form id="searchFilterForm" method="GET" action="{{ $isMemberView ? route('member.books.index') : route('books.index') }}" class="search-form">
        <input type="text" name="search" placeholder="🔍 Cari judul, pengarang, ID buku, ISBN..." value="{{ request('search') }}" class="search-input">

        @php
            $activeFilterCount = 0;
            if (request('kategori')) $activeFilterCount++;
            if (request('availability')) $activeFilterCount++;
            if (request('release_sort')) $activeFilterCount++;
            if (request('from_year')) $activeFilterCount++;
            if (request('to_year')) $activeFilterCount++;
        @endphp

        <button type="button" class="btn-filter-toggle {{ $hasFilters ? 'active' : '' }}" onclick="toggleFilterPanel()" id="filterToggleBtn">
            <span class="filter-toggle-icon">🎛️</span> Filter
            @if($activeFilterCount > 0)
                <span class="filter-badge">{{ $activeFilterCount }}</span>
            @endif
        </button>

        <button type="submit" class="btn-search">Cari</button>
        @if(request('search') && !$hasFilters)
            <a href="{{ $isMemberView ? route('member.books.index') : route('books.index') }}" class="btn-reset">Reset</a>
        @endif

        <div id="filterPanel" class="filter-panel {{ $hasFilters ? 'show' : '' }}">
            <div class="filter-panel-content">
                {{-- Kategori --}}
                <div class="filter-group">
                    <label for="filter_kategori">📂 Kategori</label>
                    <select id="filter_kategori" name="kategori" class="filter-select">
                        <option value="">Semua Kategori</option>
                        @foreach(['Fiksi', 'Non-Fiksi', 'Sains', 'Teknologi', 'Sejarah', 'Biografi', 'Lainnya'] as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="filter-group">
                    <label for="filter_availability">📋 Status</label>
                    <select id="filter_availability" name="availability" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="reference_only" {{ request('availability') == 'reference_only' ? 'selected' : '' }}>Reference Only</option>
                    </select>
                </div>

                {{-- Release Sort --}}
                <div class="filter-group">
                    <label for="filter_release_sort">📅 Urutkan Rilis</label>
                    <select id="filter_release_sort" name="release_sort" class="filter-select">
                        <option value="">Default</option>
                        <option value="newest" {{ request('release_sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="oldest" {{ request('release_sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>

                {{-- From Year --}}
                <div class="filter-group">
                    <label for="filter_from_year">📆 Dari Tahun</label>
                    <input type="number" id="filter_from_year" name="from_year" class="filter-input" placeholder="cth: 2000" min="1900" max="{{ date('Y') + 1 }}" value="{{ request('from_year') }}">
                </div>

                {{-- To Year --}}
                <div class="filter-group">
                    <label for="filter_to_year">📆 Sampai Tahun</label>
                    <input type="number" id="filter_to_year" name="to_year" class="filter-input" placeholder="cth: 2026" min="1900" max="{{ date('Y') + 1 }}" value="{{ request('to_year') }}">
                </div>
            </div>

            <div class="filter-panel-actions">
                <button type="submit" class="btn-apply-filter">✅ Apply Filter</button>
                <a href="{{ $isMemberView ? route('member.books.index') : route('books.index') }}" class="btn-reset-filter">🗑️ Reset Semua Filter</a>
            </div>
        </div>
    </form>
</div>

@if(session('success'))
    <div class="alert-success">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if((request('search') || $hasFilters) && $books->count() == 0)
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Data tidak ditemukan</h3>
        <p class="text-muted">Tidak ditemukan buku yang sesuai dengan filter atau kata kunci Anda.</p>
        <a href="{{ $isMemberView ? route('member.books.index') : route('books.index') }}" class="btn-reset-filter" style="margin-top: 12px;">🗑️ Reset Semua Filter</a>
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
                        @php($activeReservation = \App\Models\Book::activeReservationFor($book))
                        <div class="item-status-row" style="padding: 10px 22px 0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            @if($book->reference_only)
                                <span class="status-badge status-borrowed">📘 Reference Only</span>
                            @elseif($book->copy_status === 'available')
                                <span class="status-badge status-available">✅ {{ \App\Models\Book::copyStatusLabel($book) }}</span>
                            @elseif($book->copy_status === 'reserved')
                                <span class="status-badge" style="background:#fef3c7; color:#92400e;">📌 {{ \App\Models\Book::copyStatusLabel($book) }}</span>
                            @else
                                <span class="status-badge status-borrowed">⏳ {{ \App\Models\Book::copyStatusLabel($book) }}</span>
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
                                <p class="item-detail">💳 Harga: Rp {{ number_format((float) ($book->price ?? 0), 0, ',', '.') }} | ⏱️ Denda/hari: Rp {{ number_format((float) ($book->daily_late_fee ?? 0), 0, ',', '.') }}</p>
                                <p class="item-detail">📅 Maks pinjam: {{ $book->max_loan_days ?? 14 }} hari | 🔄 Maks perpanjangan: {{ $book->max_renewals ?? 1 }}x</p>
                            @endunless
                            @if($book->keterangan)
                                <p class="item-desc">{{ Str::limit($book->keterangan, 60) }}</p>
                            @endif
                        </div>
                    </div>
                </a>

                @if($isMemberView && \App\Models\Book::isReservable($book))
                    <div style="padding: 0 22px 22px;">
                        <form method="POST" action="{{ route('member.books.reserve', $book->id) }}" style="margin-top:12px;">
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

<script>
    function toggleFilterPanel() {
        const panel = document.getElementById('filterPanel');
        const toggleBtn = document.getElementById('filterToggleBtn');
        if (!panel || !toggleBtn) return;

        const isShown = panel.classList.toggle('show');
        toggleBtn.classList.toggle('active', isShown);

        // Save panel state to localStorage
        localStorage.setItem('filterPanelOpen', isShown ? '1' : '0');
    }

    // Restore panel state on page load (but only if no active filters dictate it)
    document.addEventListener('DOMContentLoaded', function () {
        const panel = document.getElementById('filterPanel');
        const toggleBtn = document.getElementById('filterToggleBtn');
        if (!panel || !toggleBtn) return;

        // If panel already has 'show' (because of active filters), don't override
        if (panel.classList.contains('show')) return;

        // Otherwise check localStorage
        if (localStorage.getItem('filterPanelOpen') === '1') {
            panel.classList.add('show');
            toggleBtn.classList.add('active');
        }
    });
</script>
@endsection
