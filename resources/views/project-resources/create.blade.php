@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="form-header">
        <div class="form-icon">🧩</div>
        <h2>Tambah Resource Proyek</h2>
        <p class="form-subtitle">Input resource dari task ProjectLibre</p>
    </div>

    <form action="{{ route('project-resources.store') }}" method="POST" class="styled-form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="task_code">Kode Task</label>
                <input type="text" id="task_code" name="task_code" value="{{ old('task_code') }}" class="form-input" placeholder="Contoh: 3.7.1">
            </div>
            <div class="form-group">
                <label for="task_name">Nama Task</label>
                <input type="text" id="task_name" name="task_name" value="{{ old('task_name') }}" class="form-input" required placeholder="Contoh: Form laporan">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="resource_name">Nama Resource</label>
                <input type="text" id="resource_name" name="resource_name" value="{{ old('resource_name') }}" class="form-input" required placeholder="Contoh: Developer Frontend">
            </div>
            <div class="form-group">
                <label for="resource_category">Kategori Resource</label>
                <select id="resource_category" name="resource_category" class="form-input" required>
                    <option value="">Pilih kategori</option>
                    @foreach(['SDM', 'Software', 'Hardware', 'Operasional', 'Testing', 'Dokumentasi', 'Lainnya'] as $category)
                        <option value="{{ $category }}" {{ old('resource_category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="quantity">Kuantitas</label>
                <input type="number" step="0.01" min="0" id="quantity" name="quantity" value="{{ old('quantity') }}" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="unit">Satuan</label>
                <input type="text" id="unit" name="unit" value="{{ old('unit') }}" class="form-input" required placeholder="Contoh: orang, lisensi, hari">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="unit_price">Harga Satuan</label>
                <input type="number" step="0.01" min="0" id="unit_price" name="unit_price" value="{{ old('unit_price') }}" class="form-input" required>
            </div>
            <div class="form-group">
                <label>Total Harga</label>
                <input type="text" id="total_preview" class="form-input" readonly placeholder="Otomatis dihitung">
            </div>
        </div>

        <div class="form-group full-width">
            <label for="notes">Catatan</label>
            <textarea id="notes" name="notes" rows="3" class="form-input textarea" placeholder="Catatan tambahan">{{ old('notes') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('project-resources.index') }}" class="btn-cancel">❌ Batal</a>
            <button type="submit" class="btn-submit">💾 Simpan Resource</button>
        </div>
    </form>
</div>

<script>
    function updateTotalPreview() {
        const quantity = parseFloat(document.getElementById('quantity').value || 0);
        const unitPrice = parseFloat(document.getElementById('unit_price').value || 0);
        document.getElementById('total_preview').value = (quantity * unitPrice).toFixed(2);
    }

    document.getElementById('quantity').addEventListener('input', updateTotalPreview);
    document.getElementById('unit_price').addEventListener('input', updateTotalPreview);
    updateTotalPreview();
</script>
@endsection
