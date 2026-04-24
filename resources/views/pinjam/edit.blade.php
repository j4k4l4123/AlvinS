@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">??</div>
        <h2>Edit Peminjaman</h2>
        <p class="form-subtitle">Update data peminjaman #{{ $pinjam->id }}</p>
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

    <form action="{{ route('pinjam.update', $pinjam->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="anggota_id"><span class="label-icon">??</span> Anggota</label>
            <select id="anggota_id" name="anggota_id" required>
                @foreach($anggota as $a)
                    <option value="{{ $a->id }}" {{ (old('anggota_id', $pinjam->anggota_id) == $a->id) ? 'selected' : '' }}>{{ $a->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="book_id"><span class="label-icon">??</span> Buku</label>
            <select id="book_id" name="book_id" required>
                @foreach($books as $b)
                    <option value="{{ $b->id }}" {{ (old('book_id', $pinjam->book_id) == $b->id) ? 'selected' : '' }}>{{ $b->judul }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_pinjam"><span class="label-icon">??</span> Tanggal Pinjam</label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', $pinjam->tanggal_pinjam->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="tanggal_kembali"><span class="label-icon">??</span> Tanggal Kembali</label>
                <input type="date" id="tanggal_kembali" name="tanggal_kembali" value="{{ old('tanggal_kembali', $pinjam->tanggal_kembali->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('pinjam.index') }}" class="btn-cancel">? Batal</a>
            <button type="submit" class="btn-submit">?? Update</button>
        </div>
    </form>
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
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
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
</style>
@endsection
