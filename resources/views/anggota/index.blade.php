@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>?? Daftar Anggota</h1>
    <a href="{{ route('anggota.create') }}" class="btn-add"><span class="icon">+</span> Tambah Anggota</a>
</div>

<div class="search-filter-box">
    <form method="GET" action="{{ route('anggota.index') }}" class="search-form">
        <input type="text" name="search" placeholder="?? Cari anggota..." value="{{ request('search') }}" class="search-input">
        <button type="submit" class="btn-search">Cari</button>
        @if(request('search'))
            <a href="{{ route('anggota.index') }}" class="btn-reset">Reset</a>
        @endif
    </form>
</div>

@if(session('success'))
    <div class="alert-success">
        <span class="alert-icon">?</span> {{ session('success') }}
    </div>
@endif

@if($anggota->count() > 0)
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="sortable">ID Anggota <span class="sort-icon">??</span></th>
                    <th class="sortable">Nama <span class="sort-icon">??</span></th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($anggota as $a)
                    <tr class="data-row">
                        <td><span class="badge-id">{{ $a->id_anggota }}</span></td>
                        <td><strong>{{ $a->nama }}</strong></td>
                        <td>{{ $a->email }}</td>
                        <td>{{ $a->telepon ?? '-' }}</td>
                        <td><span class="text-muted">{{ Str::limit($a->alamat ?? '-', 25) }}</span></td>
                        <td class="actions">
                            <a href="{{ route('anggota.edit', $a->id) }}" class="btn-action btn-edit">?? Edit</a>
                            <form action="{{ route('anggota.destroy', $a->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus anggota ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">??? Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="pagination-info">
        <span class="text-muted">Showing {{ $anggota->firstItem() ?? 0 }} to {{ $anggota->lastItem() ?? 0 }} of {{ $anggota->total() }} entries</span>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">??</div>
        <h3>Belum ada anggota</h3>
        <p class="text-muted">Tambahkan anggota pertama Anda!</p>
        <a href="{{ route('anggota.create') }}" class="btn-add"><span class="icon">+</span> Tambah Anggota</a>
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
        font-size: 1.8rem;
    }
    .btn-add {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 12px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        transition: all 0.3s;
    }
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
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
        font-weight: bold;
    }
    .search-filter-box {
        background: #dcfce7;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
        border-left: 4px solid #22c55e;
    }
    .search-form {
        display: flex;
        gap: 10px;
    }
    .search-input {
        flex: 1;
        padding: 12px 15px;
        border: 2px solid #86efac;
        border-radius: 25px;
        font-size: 14px;
        outline: none;
        transition: all 0.3s;
    }
    .search-input:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
    }
    .btn-search {
        background: #22c55e;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s;
    }
    .btn-search:hover {
        background: #16a34a;
    }
    .btn-reset {
        color: #6b7280;
        text-decoration: none;
        padding: 12px 15px;
        align-self: center;
    }
    .alert-success {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        border: 2px solid #22c55e;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        color: #15803d;
        font-weight: 500;
    }
    .alert-icon {
        margin-right: 10px;
    }
    .table-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
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
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }
    .data-table th.sortable {
        cursor: pointer;
    }
    .data-table th.sortable:hover {
        background: linear-gradient(135deg, #16a34a, #15803d);
    }
    .sort-icon {
        opacity: 0.7;
        font-size: 12px;
    }
    .data-row {
        transition: all 0.2s;
    }
    .data-row:hover {
        background: #f0fdf4;
        transform: scale(1.01);
    }
    .data-table td {
        padding: 15px;
        border-bottom: 1px solid #dcfce7;
    }
    .badge-id {
        background: #dcfce7;
        color: #15803d;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
    }
    .text-muted {
        color: #6b7280;
    }
    .actions {
        display: flex;
        gap: 8px;
    }
    .btn-action {
        padding: 8px 14px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-edit {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .btn-edit:hover {
        background: #1d4ed8;
        color: white;
    }
    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .btn-delete:hover {
        background: #dc2626;
        color: white;
    }
    .pagination-info {
        margin-top: 20px;
        text-align: center;
        padding: 15px;
        color: #6b7280;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
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
        margin-bottom: 10px;
    }
</style>
@endsection
