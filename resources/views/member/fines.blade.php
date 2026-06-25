@extends('layouts.app')

@section('title', 'Denda Saya - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Denda Saya</h1>
    <p class="text-muted">Pantau dan tandai penyelesaian denda keterlambatan.</p>
</div>

<div class="content-card" style="padding: 20px;">
    @if($fines->count())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fines as $fine)
                        <tr>
                            <td>{{ $fine->borrowing?->book?->judul ?? '-' }}</td>
                            <td>Rp {{ number_format($fine->amount, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($fine->status) }}</td>
                            <td>{{ $fine->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($fine->status === 'unpaid')
                                    <form method="POST" action="{{ route('member.fines.pay', $fine->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-action">Bayar / Tandai Lunas</button>
                                    </form>
                                @else
                                    <span class="text-muted">Paid {{ $fine->paid_at?->diffForHumans() }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 16px;">{{ $fines->links() }}</div>
    @else
        <p class="text-muted">Tidak ada denda saat ini.</p>
    @endif
</div>
@endsection
