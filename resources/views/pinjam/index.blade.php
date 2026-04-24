@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>?? Daftar Peminjaman</h1>
    <a href="{{ route('pinjam.create') }}" class="btn-add"><span class="icon">+</span> Pinjam Buku</a>
</div>

@if(session('success'))
    <div class="alert-success">
        <span class="alert-icon">?</span> {{ session('success') }}
    </div>
@endif

@if($pinjam->count() > 0)
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Anggota</th>
                    <th>Buku</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pinjam as $p)
                    <tr class="data-row">
                        <td><span class="badge">{{ $p->id }}</span></td>
                        <td>{{ $p->anggota->nama }}</td>
                        <td>{{ Str::limit($p->book->judul, 25) }}</td>
                        <td>{{ $p->tanggal_pinjam->format('d-m-Y') }}</td>
                        <td>{{ $p->tanggal_kembali->format('d-m-Y') }}</td>
                        <td>
                            @if($p->status == 'dipinjam')
                                <span class="status-badge status-borrowed">Dipinjam</span>
                            @else
                                <span class="status-badge status-returned">Dikembalikan</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a href="{{ route('pinjam.edit', $p->id) }}" class="btn-action btn-edit">?? Edit</a>
                            <form action="{{ route('pinjam.destroy', $p->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus peminjaman ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">???</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">??</div>
        <h3>Belum ada peminjaman</h3>
        <a href="{{ route('pinjam.create') }}" class="btn-add"><span class="icon">+</span> Pinjam Buku</a>
    </div>
@endif

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid #22c55e;
    }
    .page-header h1 {
        color: #15803d;
        margin: 0;
    }
    .btn-add {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 12px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
    }
    .btn-add .icon {
        background: white;
        color: #22c55e;
        width: 24px;
        height: 24px;
        display: inline-block;
        border-radius: 50%;
        text-align: center;
        line-height: 24px;
        margin-right: 8px;
    }
    .alert-success {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        border: 2px solid #22c55e;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        color: #15803d;
    }
    .table-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 2px solid #dcfce7;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table th {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 15px;
        text-align: left;
    }
    .data-row:hover {
        background: #f0fdf4;
    }
    .data-table td {
        padding: 15px;
        border-bottom: 1px solid #dcfce7;
    }
    .badge {
        background: #dcfce7;
        color: #15803d;
        padding: 5px 10px;
        border-radius: 15px;
        font-weight: 600;
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-borrowed {
        background: #fee2e2;
        color: #dc2626;
    }
    .status-returned {
        background: #dcfce7;
        color: #15803d;
    }
    .actions {
        display: flex;
        gap: 8px;
    }
    .btn-action {
        padding: 8px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        border: none;
        cursor: pointer;
    }
    .btn-edit {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .empty-state {
        text-align: center;
        padding: 60px;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border-radius: 20px;
        border: 3px dashed #86efac;
    }
    .empty-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }
    .empty-state h3 {
        color: #15803d;
    }
</style>
@endsection
