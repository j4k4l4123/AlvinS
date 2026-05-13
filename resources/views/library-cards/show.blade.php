@extends('layouts.app')

@section('title', 'Library Card Details - PerpusKu')

@section('content')
@php($isMemberCard = request()->routeIs('member.library-card'))
<div class="page-header no-print">
    <h1>{{ $isMemberCard ? 'Kartu Perpustakaan Saya' : 'Library Card Details' }}</h1>
</div>

<style>
    .library-card-print {
        max-width: 760px;
        margin: 0 auto;
        border-radius: 24px;
        padding: 28px;
        color: white;
        background: linear-gradient(135deg, #0f172a, #1d4ed8, #14b8a6);
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25);
    }
    .library-card-print .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 18px;
    }
    .library-card-print .meta-box {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 16px;
        padding: 14px;
        backdrop-filter: blur(8px);
    }
    @media print {
        .no-print, .top-navbar, .sidebar, .custom-cursor { display: none !important; }
        .main-content, .content-card, #contentCard { padding: 0 !important; margin: 0 !important; box-shadow: none !important; }
        body { background: white !important; }
        .library-card-print { box-shadow: none !important; margin-top: 20px; }
    }
</style>

<div class="library-card-print">
    <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap;">
        <div>
            <div style="font-size:14px; opacity:0.85; letter-spacing:1px; text-transform:uppercase;">PerpusKu Digital Library Card</div>
            <h2 style="margin:8px 0 6px; font-size:32px;">{{ $card->anggota?->nama ?? $card->user?->name ?? 'Unknown member' }}</h2>
            <div style="font-size:16px; opacity:0.92;">Member ID: {{ $card->anggota?->id_anggota ?? '-' }}</div>
        </div>
        <div style="text-align:right;">
            <div class="status-badge {{ $card->status === 'active' ? 'status-available' : 'status-borrowed' }}" style="background: rgba(255,255,255,0.18); color:white; border-color: rgba(255,255,255,0.25);">
                {{ ucfirst($card->status) }}
            </div>
            <div style="margin-top:12px; font-size:22px; font-weight:700;">{{ $card->card_number }}</div>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-box">
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase;">Issued Date</div>
            <div style="font-size:18px; font-weight:600; margin-top:4px;">{{ $card->issued_date?->format('d/m/Y') ?? '-' }}</div>
        </div>
        <div class="meta-box">
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase;">Expiry Date</div>
            <div style="font-size:18px; font-weight:600; margin-top:4px;">{{ $card->expiry_date?->format('d/m/Y') ?? '-' }}</div>
        </div>
        <div class="meta-box">
            <div style="font-size:12px; opacity:0.8; text-transform:uppercase;">Card Status</div>
            <div style="font-size:18px; font-weight:600; margin-top:4px;">{{ $card->isActive() ? 'Active' : 'Inactive / Expired' }}</div>
        </div>
    </div>

    <div style="margin-top: 22px; font-size: 13px; opacity: 0.9;">
        Kartu ini terhubung dengan akun anggota dan dapat digunakan sebagai identitas perpustakaan digital saat peminjaman, perpanjangan, dan verifikasi keanggotaan.
    </div>
</div>

<div class="no-print" style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
    @if($isMemberCard)
        <button onclick="window.print()" class="btn-submit" type="button">Print Card</button>
        <a href="{{ route('member.dashboard') }}" class="btn-back">Back</a>
    @else
        <a href="{{ route('library-cards.index') }}" class="btn-back">Back</a>
    @endif
</div>
@endsection
