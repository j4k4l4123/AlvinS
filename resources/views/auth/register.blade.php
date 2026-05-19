@extends('layouts.app')

@section('title', 'Register - PerpusKu')

@section('content')
<div class="content-card" style="max-width: 680px; margin: 40px auto; padding: 24px;">
    <div class="page-header" style="margin-bottom: 20px;">
        <h1>Buat Akun</h1>
        <p class="text-muted">Daftar sebagai anggota atau ajukan akses librarian yang akan ditinjau terlebih dahulu.</p>
    </div>

    @if($errors->any())
        <div class="alert-error" style="margin-bottom: 16px;">
            <ul style="margin:0; padding-left: 18px;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">
        @csrf

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="name">Nama</label>
            <input id="name" name="name" value="{{ old('name') }}" required class="search-input" />
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="search-input" />
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="role">Daftar Sebagai</label>
            <select id="role" name="role" class="search-input" onchange="toggleReasonField()" required>
                <option value="member" {{ old('role', 'member') === 'member' ? 'selected' : '' }}>Anggota</option>
                <option value="librarian" {{ old('role') === 'librarian' ? 'selected' : '' }}>Calon Librarian</option>
            </select>
        </div>

        <div class="form-group" id="reasonGroup" style="margin-bottom: 16px; display: {{ old('role') === 'librarian' ? 'block' : 'none' }};">
            <label for="reason">Alasan Mengajukan Akses Librarian</label>
            <textarea id="reason" name="reason" rows="4" class="search-input" placeholder="Tulis alasan singkat kenapa kamu membutuhkan akses librarian...">{{ old('reason') }}</textarea>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required class="search-input" />
            <small class="text-muted">Minimal 8 karakter, huruf besar, huruf kecil, angka, dan simbol.</small>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="search-input" />
        </div>

        <div style="display:flex; gap:12px; align-items:center;">
            <button type="submit" class="btn-submit">Daftar</button>
            <a href="{{ route('login') }}" class="btn-cancel">Kembali</a>
        </div>
    </form>
</div>

<script>
    function toggleReasonField() {
        const role = document.getElementById('role').value;
        const group = document.getElementById('reasonGroup');
        group.style.display = role === 'librarian' ? 'block' : 'none';
    }
</script>
@endsection
