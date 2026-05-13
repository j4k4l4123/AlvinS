@extends('layouts.app')

@section('title', 'Renewal Request Details - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Renewal Request Details</h1>
</div>

<div class="content-card" style="max-width:720px;">
    <div class="item-body">
        <h3 class="item-title">{{ $renewalRequest->anggota?->nama ?? $renewalRequest->user?->name ?? '-' }}</h3>
        <p class="item-detail">Buku: {{ $renewalRequest->borrowing?->book?->judul ?? '-' }}</p>
        <p class="item-detail">Status: {{ ucfirst($renewalRequest->status) }}</p>
        <p class="item-detail">Catatan: {{ $renewalRequest->notes }}</p>
        <p class="item-detail">Submitted: {{ $renewalRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
    </div>

    @if($renewalRequest->status === 'pending')
        <form method="POST" action="{{ route('renewal-requests.update', $renewalRequest) }}" style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="approved">
            <button type="submit" class="btn-submit">Approve</button>
        </form>

        <form method="POST" action="{{ route('renewal-requests.update', $renewalRequest) }}" style="margin-top:12px;display:flex;gap:12px;flex-wrap:wrap;">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="rejected">
            <input type="text" name="notes" class="search-input" placeholder="Optional rejection note">
            <button type="submit" class="btn-cancel">Reject</button>
        </form>
    @endif

    <div style="margin-top:20px;">
        <a href="{{ route('renewal-requests.index') }}" class="btn-back">Back</a>
    </div>
</div>
@endsection
