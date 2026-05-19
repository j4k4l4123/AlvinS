@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>🧩 Resource Proyek</h1>
    <a href="{{ route('project-resources.create') }}" class="btn-add"><span class="icon">+</span> Tambah Resource</a>
</div>

<div class="search-filter-box">
    <form method="GET" action="{{ route('project-resources.index') }}" class="search-form">
        <input type="text" name="search" placeholder="Cari task, kode, resource, kategori..." value="{{ request('search') }}" class="search-input">
        <button type="submit" class="btn-search">Cari</button>
    </form>
</div>

<div class="alert-success" style="margin-bottom: 16px;">
    <span class="alert-icon">💰</span> Total anggaran resource: <strong>Rp {{ number_format((float) $totalBudget, 0, ',', '.') }}</strong>
</div>

@if($projectResources->count())
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Kode Task</th>
                    <th>Task</th>
                    <th>Resource</th>
                    <th>Kategori</th>
                    <th>Kuantitas</th>
                    <th>Satuan</th>
                    <th>Harga Satuan</th>
                    <th>Total</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projectResources as $resource)
                    <tr>
                        <td>{{ $resource->task_code ?? '-' }}</td>
                        <td>{{ $resource->task_name }}</td>
                        <td>{{ $resource->resource_name }}</td>
                        <td>{{ $resource->resource_category }}</td>
                        <td>{{ $resource->quantity }}</td>
                        <td>{{ $resource->unit }}</td>
                        <td>Rp {{ number_format((float) $resource->unit_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format((float) $resource->total_price, 0, ',', '.') }}</td>
                        <td>{{ $resource->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">🧩</div>
        <h3>Belum ada resource proyek</h3>
        <p class="text-muted">Tambahkan resource dari task ProjectLibre di sini.</p>
    </div>
@endif
@endsection
