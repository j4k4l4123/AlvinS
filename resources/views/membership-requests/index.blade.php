@extends('layouts.app')

@section('title', 'Membership Requests - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Membership Requests</h1>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($requests->count())
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                    <tr>
                        <td>{{ $request->anggota?->nama ?? $request->user?->name ?? '-' }}</td>
                        <td>
                            @if($request->type === 'renewal')
                                Perpanjangan Peminjaman
                            @else
                                {{ ucfirst($request->type) }}
                            @endif
                        </td>
                        <td>{{ ucfirst($request->status) }}</td>
                        <td>{{ $request->type === 'renewal' ? ($request->notes ?? 'Permintaan perpanjangan') : $request->reason }}</td>
                        <td>
                            <a href="{{ route('membership-requests.show', $request->id) }}" class="btn-action">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-info" style="margin-top:20px;">
        {{ $requests->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📨</div>
        <h3>No membership requests</h3>
    </div>
@endif
@endsection
