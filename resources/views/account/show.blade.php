@extends('layouts.app')

@section('title', 'Akun Saya - PerpusKu')

@section('content')
<div class="page-header">
    <div>
        <h1>👤 Akun Saya</h1>
        <p class="text-muted">Lihat informasi akun yang sedang kamu gunakan.</p>
    </div>
</div>

<div class="content-card" style="padding: 24px; max-width: 840px;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px;">
        <div>
            <div class="text-muted">Nama</div>
            <strong>{{ $user->name }}</strong>
        </div>
        <div>
            <div class="text-muted">Email</div>
            <strong>{{ $user->email }}</strong>
        </div>
        <div>
            <div class="text-muted">Role</div>
            <strong>
                @if($user->isLibrarian() && $user->isMember())
                    Librarian, Anggota
                @elseif($user->isLibrarian())
                    Librarian
                @else
                    Anggota
                @endif
            </strong>
        </div>
        <div>
            <div class="text-muted">Terdaftar Sejak</div>
            <strong>{{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
        </div>
        <div>
            <div class="text-muted">ID Anggota</div>
            <strong>{{ $anggota?->id_anggota ?? $profile?->id_anggota ?? '-' }}</strong>
        </div>
        <div>
            <div class="text-muted">Kartu Perpustakaan</div>
            <strong>{{ $libraryCard?->card_number ?? 'Belum tersedia' }}</strong>
        </div>
        <div>
            <div class="text-muted">Peminjaman Aktif</div>
            <strong>{{ $activeBorrowingsCount }}</strong>
        </div>
        <div>
            <div class="text-muted">Total Denda</div>
            <strong>Rp {{ number_format($totalFines, 0, ',', '.') }}</strong>
        </div>
    </div>

    @if($profile || $anggota)
        <div style="margin-top:20px; padding:16px; border-radius:16px; background:rgba(236,253,245,0.7);">
            <div class="text-muted" style="margin-bottom:10px;">Profil Anggota</div>
            <div><strong>Nama:</strong> {{ $profile?->nama ?? $anggota?->nama ?? '-' }}</div>
            <div><strong>Alamat:</strong> {{ $profile?->alamat ?? $anggota?->alamat ?? '-' }}</div>
            <div><strong>No. Telepon:</strong> {{ $profile?->no_tlp ?? $anggota?->no_tlp ?? '-' }}</div>
            <div><strong>Status Member:</strong> {{ ucfirst(str_replace('_', ' ', $profile?->membership_status ?? 'active')) }}</div>
        </div>
    @endif

    @if($user->isMember())
        <div style="margin-top:20px; padding:16px; border-radius:16px; background:rgba(255,255,255,0.68); border:1px solid rgba(52,211,153,0.18);">
            <div class="text-muted" style="margin-bottom:8px;">Pengaturan Akun</div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="{{ route('member.profile.edit') }}" class="btn-submit">Edit Profil</a>
                <a href="{{ route('member.library-card') }}" class="btn-submit">Lihat Kartu</a>
            </div>
        </div>
    @endif

</div>
@endsection
