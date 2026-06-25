<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Book;
use App\Models\Pinjam;
use Illuminate\Support\Facades\DB;
use Exception;

class LibraryPolicyService
{
    public const MAX_BORROWINGS = 5;
    public const MAX_PENDING_RESERVATIONS = 3;

    public function assertCanBorrow(object $anggota, object $book): void
    {
        if ($book->reference_only) {
            throw new Exception('Buku ini hanya untuk referensi dan tidak bisa dipinjam.');
        }

        if (in_array($book->copy_status, ['lost', 'damaged', 'maintenance'], true)) {
            throw new Exception('Buku ini sedang tidak tersedia karena status copy: ' . $book->copy_status . '.');
        }

        if (! Book::isAvailable($book)) {
            throw new Exception('Stok buku sedang tidak tersedia.');
        }

        if (DB::table('pinjam')->where('anggota_id', $anggota->id)->where('status', 'dipinjam')->count() >= self::MAX_BORROWINGS) {
            throw new Exception('Anggota sudah mencapai batas maksimal peminjaman.');
        }

        $hasOverdue = DB::table('pinjam')->where('anggota_id', $anggota->id)
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', now()->toDateString())
            ->exists();

        if ($hasOverdue) {
            throw new Exception('Anggota masih memiliki peminjaman yang terlambat.');
        }

        $unpaidFines = DB::table('fines')
            ->where('anggota_id', $anggota->id)
            ->where('status', 'unpaid')
            ->exists();

        if ($unpaidFines) {
            throw new Exception('Anggota masih memiliki denda yang belum dibayar.');
        }

        $libraryCard = Anggota::libraryCardFor($anggota);
        if ($libraryCard && !\App\Models\LibraryCard\LibraryCard::isActive($libraryCard)) {
            throw new Exception('Kartu anggota tidak aktif atau sudah kedaluwarsa.');
        }
    }

    public function assertCanReserve(object $anggota, object $book): void
    {
        if ($book->reference_only) {
            throw new Exception('Buku ini hanya untuk referensi dan tidak bisa direservasi.');
        }

        if (Book::canBeBorrowed($book)) {
            throw new Exception('Buku ini masih tersedia untuk dipinjam langsung, jadi reservasi belum diperlukan.');
        }

        $pendingReservations = DB::table('book_reservations')
            ->where('anggota_id', $anggota->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->count();

        if ($pendingReservations >= self::MAX_PENDING_RESERVATIONS) {
            throw new Exception('Anggota sudah mencapai batas maksimal antrean reservasi.');
        }
    }

    public function assertCanRenew(object $pinjam): void
    {
        if ($pinjam->status !== 'dipinjam') {
            throw new Exception('Peminjaman ini tidak aktif.');
        }

        if (Pinjam::isOverdue($pinjam)) {
            throw new Exception('Buku yang terlambat tidak bisa diperpanjang.');
        }

        $book = Book::find($pinjam->book_id);
        $maxRenewals = $book?->max_renewals ?? 1;

        if (($pinjam->renewal_count ?? 0) >= $maxRenewals) {
            throw new Exception('Batas maksimal perpanjangan sudah tercapai.');
        }
    }

    public function assertCanMarkLostOrDamaged(object $pinjam): void
    {
        if ($pinjam->status !== 'dipinjam') {
            throw new Exception('Hanya peminjaman aktif yang bisa ditandai hilang atau rusak.');
        }

        if ($pinjam->lost_at || $pinjam->damaged_at || in_array($pinjam->status, ['hilang', 'rusak'], true)) {
            throw new Exception('Peminjaman ini sudah pernah ditandai hilang atau rusak.');
        }
    }
}
