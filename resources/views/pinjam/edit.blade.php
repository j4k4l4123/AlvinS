@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">✏️</div>
        <h2>Edit Peminjaman</h2>
        <p class="form-subtitle">Update data peminjaman #{{ $pinjam->id }}</p>
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
    @endif

    <form action="{{ route('pinjam.update', $pinjam->id) }}" method="POST" class="styled-form">
        @csrf
        @method('PUT')

        <div class="form-group full-width">
            <label for="anggota_id"><span class="label-icon">👤</span> Anggota</label>
            <select id="anggota_id" name="anggota_id" required class="form-input">
                @foreach($anggota as $a)
                    <option value="{{ $a->id }}" {{ (old('anggota_id', $pinjam->anggota_id) == $a->id) ? 'selected' : '' }}>{{ $a->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group full-width">
            <label for="book_id"><span class="label-icon">📚</span> Buku</label>
            <select id="book_id" name="book_id" required class="form-input">
                @foreach($books as $b)
                    <option value="{{ $b->id }}" {{ (old('book_id', $pinjam->book_id) == $b->id) ? 'selected' : '' }}>{{ $b->judul }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_pinjam"><span class="label-icon">📅</span> Tanggal Pinjam</label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', $pinjam->tanggal_pinjam->format('Y-m-d')) }}" required class="form-input">
            </div>

            <div class="form-group">
                <label for="tanggal_kembali"><span class="label-icon">🎯</span> Tanggal Kembali</label>
                <input type="date" id="tanggal_kembali" name="tanggal_kembali" value="{{ old('tanggal_kembali', $pinjam->tanggal_kembali->format('Y-m-d')) }}" required class="form-input">
            </div>

        <div class="form-actions">
            <a href="{{ route('pinjam.index') }}" class="btn-cancel">❌ Batal</a>
            <button type="submit" class="btn-submit">
                <span class="btn-icon">💾</span>
                Update
            </button>
        </div>
    </form>
</div>

<style>
    .form-container {
        max-width: 700px;
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
        font-size: 1.8rem;
    }
    .form-subtitle {
        color: #22c55e;
        margin: 10px 0 0;
    }
    .alert-error {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        border: 2px solid #ef4444;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
        color: #dc2626;
    }
    .alert-error .alert-icon {
        font-size: 24px;
    }
    .error-list {
        margin: 10px 0 0 20px;
        padding: 0;
    }
    .error-list li {
        margin-bottom: 5px;
    }
    .styled-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
    }
    .form-group.full-width {
        grid-column: 1 / -1;
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
    .form-input {
        padding: 14px 18px;
        border: 2px solid #bbf7d0;
        border-radius: 12px;
        font-size: 15px;
        background: white;
        transition: all 0.3s;
        width: 100%;
    }
    .form-input:focus {
        outline: none;
        border-color: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
    }
    select.form-input {
        cursor: pointer;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
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
        transition: all 0.3s;
    }
    .btn-cancel:hover {
        background: #dc2626;
        color: white;
    }
    .btn-submit {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        transition: all 0.3s;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
    }
    .btn-icon {
        font-size: 18px;
    }
</style>
@endsection
