@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>👥 Daftar Anggota</h1>
    <a href="{{ route('anggota.create') }}" class="btn-add"><span class="icon">+</span> Tambah Anggota</a>
</div>

@if(session('success'))
    <div class="alert-success">
        <span class="alert-icon">✅</span> {{ session('success') }}
    </div>
@endif

@if($anggota->count() > 0)
    <div class="items-grid">
        @foreach($anggota as $a)
            <div class="item-card">
                <div class="item-header">
                    <span class="badge-id">{{ $a->id_anggota }}</span>
                    <span class="item-date">{{ $a->tanggal_daftar ? $a->tanggal_daftar->format('d-m-Y') : '-' }}</span>
                </div>

                <div class="item-body">
                    <h3 class="item-title">{{ $a->nama }}</h3>
                    <p class="item-detail">📞 {{ $a->no_tlp ?? '-' }}</p>
                    <p class="item-detail">📍 {{ Str::limit($a->alamat ?? '-', 40) }}</p>
                </div>

                <div class="item-actions">
                    <a href="{{ route('anggota.edit', $a->id) }}" class="btn-action btn-edit">✏️ Edit</a>
                    <form action="{{ route('anggota.destroy', $a->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus anggota ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action btn-delete">🗑️ Hapus</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-info">
        <span class="text-muted">Showing {{ $anggota->count() }} anggota</span>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">📭</div>
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
    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    .item-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 2px solid #dcfce7;
        transition: all 0.3s;
    }
    .item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(34, 197, 94, 0.15);
        border-color: #86efac;
    }
    .item-header {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .badge-id {
        background: #22c55e;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .item-date {
        color: #15803d;
        font-size: 13px;
        font-weight: 600;
    }
    .item-body {
        padding: 20px;
    }
    .item-title {
        color: #15803d;
        margin: 0 0 10px;
        font-size: 1.2rem;
        line-height: 1.4;
    }
    .item-detail {
        color: #6b7280;
        margin: 5px 0;
        font-size: 14px;
    }
    .item-actions {
        padding: 15px 20px;
        background: #f0fdf4;
        display: flex;
        gap: 10px;
    }
    .item-actions form {
        flex: 1;
        display: flex;
    }
    .btn-action {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
        width: 100%;
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
    .text-muted {
        color: #6b7280;
        margin-bottom: 20px;
    }
</style>
@endsection

