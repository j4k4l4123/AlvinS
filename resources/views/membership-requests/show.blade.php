@extends('layouts.app')

@section('title', 'Membership Request Details - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Membership Request Details</h1>
</div>

<div class="content-card" style="max-width:720px;">
    <div class="item-body">
        <h3 class="item-title">{{ $membershipRequest->anggota?->nama ?? $membershipRequest->user?->name ?? '-' }}</h3>
        <p class="item-detail">Type: {{ $membershipRequest->type === 'renewal' ? 'Perpanjangan Peminjaman' : ucfirst($membershipRequest->type) }}</p>
        <p class="item-detail">Status: {{ ucfirst($membershipRequest->status) }}</p>
        <p class="item-detail">Reason: {{ $membershipRequest->type === 'renewal' ? ($membershipRequest->notes ?? 'Permintaan perpanjangan') : $membershipRequest->reason }}</p>
        <p class="item-detail">Submitted: {{ $membershipRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
    </div>

    @if($membershipRequest->status === 'pending')
        <form method="POST" action="{{ route('membership-requests.update', $membershipRequest->id) }}" style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="approved">
            <button type="submit" class="btn-submit">Approve</button>
        </form>

        <form method="POST" action="{{ route('membership-requests.update', $membershipRequest->id) }}" style="margin-top:12px;display:flex;gap:12px;flex-wrap:wrap;">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="rejected">
            <input type="text" name="notes" class="search-input" placeholder="Optional rejection note">
            <button type="submit" class="btn-cancel">Reject</button>
        </form>
    @endif

    <div style="margin-top:20px;">
        <a href="{{ route('membership-requests.index') }}" class="btn-back">Back</a>
    </div>
</div>
@endsection
