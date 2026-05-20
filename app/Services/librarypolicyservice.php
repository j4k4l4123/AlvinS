<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Book;
use App\Models\Pinjam;
use Exception;

class LibraryPolicyService
{
    public const MAX_BORROWINGS = 5;
    public const MAX_PENDING_RESERVATIONS = 3;

    public function assertCanBorrow(Anggota $anggota, Book $book): void
    {
        if ($book->reference_only) {
            throw new Exception('Buku ini hanya untuk referensi dan tidak bisa dipinjam.');
        }

        if (in_array($book->copy_status, ['lost', 'damaged', 'maintenance'], true)) {
            throw new Exception('Buku ini sedang tidak tersedia karena status copy: ' . $book->copy_status . '.');
        }

        if (! $book->isAvailable()) {
            throw new Exception('Stok buku sedang tidak tersedia.');
        }

        if ($anggota->activeBorrowings()->count() >= self::MAX_BORROWINGS) {
            throw new Exception('Anggota sudah mencapai batas maksimal peminjaman.');
        }

        $hasOverdue = Pinjam::where('anggota_id', $anggota->id)
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', now()->toDateString())
            ->exists();

        if ($hasOverdue) {
            throw new Exception('Anggota masih memiliki peminjaman yang terlambat.');
        }

        $unpaidFines = $anggota->pengembalian()->whereHas('fine', function ($query) {
            $query->where('status', 'unpaid');
        })->exists();

        if ($unpaidFines) {
            throw new Exception('Anggota masih memiliki denda yang belum dibayar.');
        }

        if ($anggota->libraryCard && ! $anggota->libraryCard->isActive()) {
            throw new Exception('Kartu anggota tidak aktif atau sudah kedaluwarsa.');
        }
    }

    public function assertCanReserve(Anggota $anggota, Book $book): void
    {
        if ($book->reference_only) {
            throw new Exception('Buku ini hanya untuk referensi dan tidak bisa direservasi.');
        }

        if ($book->canBeBorrowed()) {
            throw new Exception('Buku ini masih tersedia untuk dipinjam langsung, jadi reservasi belum diperlukan.');
        }

        $pendingReservations = $anggota->reservations()
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->count();

        if ($pendingReservations >= self::MAX_PENDING_RESERVATIONS) {
            throw new Exception('Anggota sudah mencapai batas maksimal antrean reservasi.');
        }
    }

    public function assertCanRenew(Pinjam $pinjam): void
    {
        if ($pinjam->status !== 'dipinjam') {
            throw new Exception('Peminjaman ini tidak aktif.');
        }

        if ($pinjam->isOverdue()) {
            throw new Exception('Buku yang terlambat tidak bisa diperpanjang.');
        }

        if (($pinjam->renewal_count ?? 0) >= ($pinjam->book?->max_renewals ?? 1)) {
            throw new Exception('Batas maksimal perpanjangan sudah tercapai.');
        }
    }

    public function assertCanMarkLostOrDamaged(Pinjam $pinjam): void
    {
        if ($pinjam->status !== 'dipinjam') {
            throw new Exception('Hanya peminjaman aktif yang bisa ditandai hilang atau rusak.');
        }

        if ($pinjam->lost_at || $pinjam->damaged_at || in_array($pinjam->status, ['hilang', 'rusak'], true)) {
            throw new Exception('Peminjaman ini sudah pernah ditandai hilang atau rusak.');
        }
    }
}
