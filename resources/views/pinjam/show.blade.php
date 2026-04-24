@extends('layouts.app')

@section('content')
    <h2>Detail Peminjaman</h2>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px;">
        <p><strong>ID Peminjaman:</strong> {{ $pinjam->id }}</p>
        <p><strong>ID Anggota:</strong> {{ $pinjam->anggota->id_anggota }}</p>
        <p><strong>Nama Anggota:</strong> {{ $pinjam->anggota->nama }}</p>
        <p><strong>ID Buku:</strong> {{ $pinjam->book->id_buku }}</p>
        <p><strong>Judul Buku:</strong> {{ $pinjam->book->judul }}</p>
        <p><strong>Tanggal Pinjam:</strong> {{ $pinjam->tanggal_pinjam->format('d-m-Y') }}</p>
        <p><strong>Tanggal Kembali:</strong> {{ $pinjam->tanggal_kembali->format('d-m-Y') }}</p>
        <p><strong>Status:</strong> 
            @if($pinjam->status == 'dipinjam')
                <span style="color: #dc3545; font-weight: bold;">Dipinjam</span>
            @else
                <span style="color: #28a745; font-weight: bold;">Dikembalikan</span>
            @endif
        </p>
        <p><strong>Dibuat:</strong> {{ $pinjam->created_at->format('d-m-Y H:i') }}</p>
        <p><strong>Diupdate:</strong> {{ $pinjam->updated_at->format('d-m-Y H:i') }}</p>
    </div>

    <div style="margin-top: 20px;">
        <a href="{{ route('pinjam.index') }}" class="btn">Kembali</a>
        <a href="{{ route('pinjam.edit', $pinjam->id) }}" class="btn" style="background-color: #ffc107; margin-left: 10px;">Edit</a>
        @if($pinjam->status == 'dipinjam')
            <a href="{{ route('pengembalian.create') }}" class="btn" style="background-color: #28a745; margin-left: 10px;">Kembalikan Buku</a>
        @endif
    </div>
@endsection
