@extends('layouts.app')

@section('title', 'Notifikasi - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Notifikasi</h1>
    <p class="text-muted">Lihat pemberitahuan reservasi, keterlambatan, dan pembaruan akun.</p>
</div>

<div class="content-card" style="padding: 20px;">
    @if($notifications->count())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $notification)
                        <tr>
                            <td>{{ $notification->title }}</td>
                            <td>{{ $notification->message }}</td>
                            <td>{{ $notification->read_at ? 'Read' : 'Unread' }}</td>
                            <td>{{ $notification->created_at?->diffForHumans() }}</td>
                            <td>
                                @if(! $notification->read_at)
                                    <form method="POST" action="{{ route('member.notifications.read', $notification) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-action">Tandai Dibaca</button>
                                    </form>
                                @else
                                    <span class="text-muted">Done</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 16px;">{{ $notifications->links() }}</div>
    @else
        <p class="text-muted">Belum ada notifikasi.</p>
    @endif
</div>
@endsection
