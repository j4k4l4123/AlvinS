@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>🔐 Vigenère Cipher</h1>
    <div class="page-header-actions" style="gap: 10px; display:flex; align-items:center;">
        <a href="{{ route('vigenere.index') }}" class="btn-submit" style="text-decoration:none;">
            ♻️ Reset
        </a>
    </div>
</div>

@if(session('vigenere_error'))
    <div class="alert-error" style="margin:16px 0; padding:12px 16px; border-radius:12px; background: rgba(248,113,113,0.15); border:1px solid rgba(248,113,113,0.25);">
        <strong>⚠️ {{ session('vigenere_error') }}</strong>
    </div>
@endif

@if($result)
    <div class="alert-success" style="margin:16px 0; padding:12px 16px; border-radius:12px; background: rgba(52,211,153,0.10); border:1px solid rgba(52,211,153,0.22);">
        <div style="font-weight:800; margin-bottom:8px;">
            Hasil {{ ($mode === 'decrypt') ? 'Dekripsi' : 'Enkripsi' }}
        </div>
        <textarea readonly rows="5" style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(52,211,153,0.22); background: rgba(255,255,255,0.65); font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">{{ $result }}</textarea>
    </div>
@endif

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-top: 16px;">
    <div class="content-card" style="padding:16px;">
        <h2 style="margin:0 0 10px;">✉️ Encrypt</h2>
        <form method="POST" action="{{ route('vigenere.encrypt') }}">
            @csrf
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-weight:700;">Plaintext (bisa berisi spasi & spesial)</label>
                <textarea name="plaintext" rows="5" required class="form-input textarea" style="width:100%;">{{ old('plaintext') }}</textarea>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-weight:700;">Key (pakai huruf A-Z; case-insensitive)</label>
                <input name="key" type="text" required class="form-input" value="{{ old('key') }}" placeholder="Contoh: KEY" />
            </div>
            <button type="submit" class="btn-submit" style="width:100%; justify-content:center; display:flex;">
                🔒 Enkripsi
            </button>
        </form>
        <div style="margin-top:10px; font-size:12px; color:#475569;">
            Catatan: karakter selain huruf A-Z/a-z (spasi, angka, spesial) dipertahankan seperti aslinya.
        </div>
    </div>

    <div class="content-card" style="padding:16px;">
        <h2 style="margin:0 0 10px;">🔓 Decrypt</h2>
        <form method="POST" action="{{ route('vigenere.decrypt') }}">
            @csrf
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-weight:700;">Ciphertext (bisa berisi spasi & spesial)</label>
                <textarea name="ciphertext" rows="5" required class="form-input textarea" style="width:100%;">{{ old('ciphertext') }}</textarea>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-weight:700;">Key (pakai huruf A-Z; case-insensitive)</label>
                <input name="key" type="text" required class="form-input" value="{{ old('key') }}" placeholder="Contoh: KEY" />
            </div>
            <button type="submit" class="btn-submit" style="width:100%; justify-content:center; display:flex;">
                🧩 Dekripsi
            </button>
        </form>
        <div style="margin-top:10px; font-size:12px; color:#475569;">
            Catatan: karakter selain huruf A-Z/a-z (spasi, angka, spesial) dipertahankan seperti aslinya.
        </div>
    </div>
</div>
@endsection

