@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">📥</div>
        <h2>Proses Pengembalian</h2>
        <p class="form-subtitle">Pilih peminjaman yang akan dikembalikan</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <span class="alert-icon">⚠️</span>
            <div>
                <strong>Oops! Ada kesalahan:</strong>
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($pinjamList->count() > 0)
        <form action="{{ route('pengembalian.store') }}" method="POST" class="styled-form">
            @csrf

            <div class="form-group full-width">
                <label for="pinjam_id"><span class="label-icon">📋</span> Pilih Peminjaman</label>
                <select id="pinjam_id" name="pinjam_id" required class="form-input">
                    <option value="">-- Pilih Peminjaman --</option>
                    @foreach($pinjamList as $p)
                        <option value="{{ $p->id }}" {{ old('pinjam_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->anggota->nama }} - {{ Str::limit($p->book->judul, 30) }}
                            (Kembali: {{ $p->tanggal_kembali->format('d-m-Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group full-width">
                <label for="tanggal_dikembalikan"><span class="label-icon">📅</span> Tanggal Dikembalikan</label>
                <input type="date" id="tanggal_dikembalikan" name="tanggal_dikembalikan" value="{{ old('tanggal_dikembalikan', date('Y-m-d')) }}" required class="form-input">
            </div>

            <div class="info-box">
                <span class="info-icon">💡</span> Denda dihitung otomatis: Rp 5.000 per hari keterlambatan
            </div>

            <div class="form-actions">
                <a href="{{ route('pengembalian.index') }}" class="btn-cancel">❌ Batal</a>
                <button type="submit" class="btn-submit">
                    <span class="btn-icon">✅</span>
                    Proses Kembali
                </button>
            </div>
        </form>
    @else
        <div class="alert-info">
            <p>📭 Tidak ada peminjaman yang aktif.</p>
        </div>
        <a href="{{ route('pengembalian.index') }}" class="btn-back" style="display:block;text-align:center;margin-top:15px;">Kembali</a>
    @endif
</div>
@endsection

