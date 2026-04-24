@extends('layouts.app')

@section('content')
    <h2>Anggota Details</h2>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px;">
        <p><strong>ID Anggota:</strong> {{ $anggota->id_anggota }}</p>
        <p><strong>Nama:</strong> {{ $anggota->nama }}</p>
        <p><strong>Alamat:</strong> {{ $anggota->alamat }}</p>
        <p><strong>No Telepon:</strong> {{ $anggota->no_tlp }}</p>
        <p><strong>Tanggal Daftar:</strong> {{ $anggota->tanggal_daftar->format('d-m-Y') }}</p>
        <p><strong>Dibuat:</strong> {{ $anggota->created_at->format('d-m-Y H:i') }}</p>
        <p><strong>Diupdate:</strong> {{ $anggota->updated_at->format('d-m-Y H:i') }}</p>
    </div>

    <div style="margin-top: 20px;">
        <a href="{{ route('anggota.index') }}" class="btn">Back to List</a>
        <a href="{{ route('anggota.edit', $anggota->id) }}" class="btn" style="background-color: #ffc107; margin-left: 10px;">Edit</a>
    </div>
@endsection
