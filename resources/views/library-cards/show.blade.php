@extends('layouts.app')

@section('title', 'Library Card Details - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Library Card Details</h1>
</div>

<div class="content-card" style="max-width:720px;">
    <div class="item-header">
        <span class="status-badge {{ $card->status === 'active' ? 'status-available' : 'status-borrowed' }}">
            {{ ucfirst($card->status) }}
        </span>
        <span class="item-id">{{ $card->card_number }}</span>
    </div>

    <div class="item-body">
        <h3 class="item-title">{{ $card->anggota?->nama ?? $card->user?->name ?? 'Unknown member' }}</h3>
        <p class="item-detail">Member ID: {{ $card->anggota?->id_anggota ?? '-' }}</p>
        <p class="item-detail">Issued: {{ $card->issued_date?->format('d/m/Y') ?? '-' }}</p>
        <p class="item-detail">Expires: {{ $card->expiry_date?->format('d/m/Y') ?? '-' }}</p>
        <p class="item-detail">Active now: {{ $card->isActive() ? 'Yes' : 'No' }}</p>
    </div>

    <div style="margin-top:20px;">
        <a href="{{ route('library-cards.index') }}" class="btn-back">Back</a>
    </div>
</div>
@endsection
