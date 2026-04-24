@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">??</div>
        <h2>Proses Pengembalian</h2>
        <p class="form-subtitle">Pilih peminjaman yang akan dikembalikan</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <span class="alert-icon">??</span>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($pinjamList->count() > 0)
        <form action="{{ route('pengembalian.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="pinjam_id"><span class="label-icon">??</span> Pilih Peminjaman</label>
                <select id="pinjam_id" name="pinjam_id" required>
                    <option value="">-- Pilih Peminjaman --</option>
                    @foreach($pinjamList as $p)
                        <option value="{{ $p->id }}" {{ old('pinjam_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->anggota->nama }} - {{ Str::limit($p->book->judul, 30) }} 
                            (Kembali: {{ $p->tanggal_kembali->format('d-m-Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="tanggal_dikembalikan"><span class="label-icon">??</span> Tanggal Dikembalikan</label>
                <input type="date" id="tanggal_dikembalikan" name="tanggal_dikembalikan" value="{{ old('tanggal_dikembalikan', date('Y-m-d')) }}" required>
            </div>

            <div class="info-box">
                <p>?? Denda dihitung otomatis: Rp 5.000 per hari keterlambatan</p>
            </div>

            <div class="form-actions">
                <a href="{{ route('pengembalian.index') }}" class="btn-cancel">? Batal</a>
                <button type="submit" class="btn-submit">?? Proses</button>
            </div>
        </form>
    @else
        <div class="alert-info">
            <p>Tidak ada peminjaman yang aktif.</p>
        </div>
        <a href="{{ route('pengembalian.index') }}" class="btn-back">Kembali</a>
    @endif
</div>

<style>
    .form-container {
        max-width: 600px;
        margin: 0 auto;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border-radius: 24px;
        padding: 40px;
        border: 3px solid #86efac;
        box-shadow: 0 20px 60px rgba(34, 197, 94, 0.15);
    }
    .form-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .form-icon {
        font-size: 60px;
        margin-bottom: 15px;
    }
    .form-header h2 {
        color: #15803d;
        margin: 0;
    }
    .form-subtitle {
        color: #22c55e;
    }
    .alert-error {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        border: 2px solid #ef4444;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
        color: #dc2626;
    }
    .alert-info {
        background: #dbeafe;
        border: 2px solid #3b82f6;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        color: #1d4ed8;
        text-align: center;
    }
    form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .form-group label {
        color: #15803d;
        font-weight: 600;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .label-icon {
        font-size: 18px;
    }
    select, input {
        padding: 14px 18px;
        border: 2px solid #bbf7d0;
        border-radius: 12px;
        font-size: 15px;
        background: white;
        width: 100%;
    }
    select:focus, input:focus {
        outline: none;
        border-color: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
    }
    .info-box {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 15px;
        border-radius: 8px;
        color: #92400e;
    }
    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 10px;
        padding-top: 20px;
        border-top: 2px dashed #86efac;
    }
    .btn-cancel {
        background: #fee2e2;
        color: #dc2626;
        padding: 14px 28px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
    }
    .btn-submit {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
    }
    .btn-back {
        display: block;
        text-align: center;
        background: #dcfce7;
        color: #15803d;
        padding: 14px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
    }
</style>
@endsection
