@extends('layouts.app')

@section('title', 'Renewal Requests - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Renewal Requests</h1>
</div>

@if($requests->count())
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Buku</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                    <tr>
                        <td>{{ $request->anggota?->nama ?? $request->user?->name ?? '-' }}</td>
                        <td>{{ $request->borrowing?->book?->judul ?? '-' }}</td>
                        <td>{{ ucfirst($request->status) }}</td>
                        <td>{{ $request->notes }}</td>
                        <td><a href="{{ route('renewal-requests.show', $request) }}" class="btn-action">View</a></td>
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
        <div class="empty-icon">🔄</div>
        <h3>No renewal requests</h3>
    </div>
@endif
@endsection
