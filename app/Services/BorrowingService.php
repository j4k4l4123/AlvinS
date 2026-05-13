<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Book;
use App\Models\BookReservation;
use App\Models\LibraryCard\LibraryCard;
use App\Models\Pinjam;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BorrowingService
{
    public const MAX_BORROWINGS = 5;
    public const DEFAULT_BORROW_DAYS = 14;

    public function borrow(
        int $anggotaId,
        int $bookId,
        ?string $tanggalPinjam = null,
        ?string $tanggalKembali = null
    ): Pinjam {
        return DB::transaction(function () use ($anggotaId, $bookId, $tanggalPinjam, $tanggalKembali) {
            BookReservation::where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            $book = Book::findOrFail($bookId);
            $anggota = Anggota::with(['user.memberProfile', 'libraryCard'])->findOrFail($anggotaId);

            if ($book->reference_only) {
                throw new \Exception('Buku "' . $book->judul . '" hanya untuk referensi dan tidak bisa dipinjam.');
            }

            $activeReservation = BookReservation::where('book_id', $book->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('expires_at', '>', now())
                ->first();

            if ($activeReservation && $activeReservation->status === 'pending') {
                throw new \Exception('Buku ini sedang menunggu persetujuan reservasi librarian.');
            }

            if ($activeReservation && (int) $activeReservation->anggota_id !== (int) $anggotaId) {
                throw new \Exception('Buku ini lagi di reservasi. Hanya anggota yang reservasinya disetujui yang boleh dipinjamkan buku ini.');
            }

            if (! $book->isAvailable()) {
                throw new \Exception('Buku "' . $book->judul . '" sedang dipinjam oleh anggota lain atau stok tidak tersedia.');
            }

            $activeBorrows = Pinjam::where('anggota_id', $anggotaId)
                ->where('status', 'dipinjam')
                ->count();

            if ($activeBorrows >= self::MAX_BORROWINGS) {
                throw new \Exception('Anggota sudah mencapai batas peminjaman maksimum (' . self::MAX_BORROWINGS . ' buku).');
            }

            $user = $anggota->user;
            $memberProfile = $user?->memberProfile;

            if ($memberProfile && ($memberProfile->membership_status ?? 'active') !== 'active') {
                throw new \Exception('Status keanggotaan anggota tidak aktif. Tidak dapat meminjam buku.');
            }

            if ($anggota->libraryCard && ! $anggota->libraryCard->isActive()) {
                throw new \Exception('Kartu perpustakaan anggota tidak aktif atau sudah kedaluwarsa.');
            }

            $borrowDate = $tanggalPinjam ? Carbon::parse($tanggalPinjam) : Carbon::today();
            $returnDate = $tanggalKembali
                ? Carbon::parse($tanggalKembali)
                : $borrowDate->copy()->addDays(self::DEFAULT_BORROW_DAYS);

            $pinjam = Pinjam::create([
                'anggota_id' => $anggotaId,
                'book_id' => $bookId,
                'tanggal_pinjam' => $borrowDate->toDateString(),
                'tanggal_kembali' => $returnDate->toDateString(),
                'status' => 'dipinjam',
            ]);

            if ($activeReservation && (int) $activeReservation->anggota_id === (int) $anggotaId) {
                $activeReservation->update(['status' => 'completed']);
            }

            return $pinjam;
        });
    }

    public function borrowByBarcodes(string $cardNumber, string $bookBarcode): Pinjam
    {
        $card = LibraryCard::with(['anggota.user.memberProfile'])->where('card_number', $cardNumber)->first();

        if (! $card) {
            throw new \Exception('Barcode kartu perpustakaan tidak ditemukan.');
        }

        if (! $card->isActive()) {
            throw new \Exception('Kartu perpustakaan tidak aktif atau sudah kedaluwarsa.');
        }

        $book = Book::where('id_buku', $bookBarcode)->first();

        if (! $book) {
            throw new \Exception('Barcode buku tidak ditemukan.');
        }

        return $this->borrow($card->anggota_id, $book->id);
    }

    public function reserve(int $anggotaId, int $bookId, int $userId): BookReservation
    {
        return DB::transaction(function () use ($anggotaId, $bookId, $userId) {
            BookReservation::where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            $book = Book::findOrFail($bookId);
            $anggota = Anggota::with(['user.memberProfile', 'libraryCard'])->findOrFail($anggotaId);

            if ($book->reference_only) {
                throw new \Exception('Buku ini hanya untuk referensi dan tidak bisa direservasi.');
            }

            if (! $book->isAvailable()) {
                throw new \Exception('Buku ini sedang dipinjam.');
            }

            $user = $anggota->user;
            $memberProfile = $user?->memberProfile;

            if ($memberProfile && ($memberProfile->membership_status ?? 'active') !== 'active') {
                throw new \Exception('Status keanggotaan anggota tidak aktif. Tidak dapat melakukan reservasi.');
            }

            if ($anggota->libraryCard && ! $anggota->libraryCard->isActive()) {
                throw new \Exception('Kartu perpustakaan anggota tidak aktif atau sudah kedaluwarsa.');
            }

            $activeReservation = BookReservation::where('book_id', $bookId)
                ->whereIn('status', ['pending', 'approved'])
                ->where('expires_at', '>', now())
                ->first();

            if ($activeReservation && (int) $activeReservation->anggota_id !== (int) $anggotaId) {
                throw new \Exception('Buku ini lagi di reservasi.');
            }

            $existingOwn = BookReservation::where('book_id', $bookId)
                ->where('anggota_id', $anggotaId)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->first();

            if ($existingOwn) {
                throw new \Exception('Kamu sudah mereservasi buku ini.');
            }

            return BookReservation::create([
                'user_id' => $userId,
                'anggota_id' => $anggotaId,
                'book_id' => $bookId,
                'status' => 'pending',
                'expires_at' => now()->addDay(),
            ]);
        });
    }

    public function cancel(int $pinjamId): void
    {
        Pinjam::destroy($pinjamId);
    }

    public function getActiveBorrowings(int $anggotaId)
    {
        return Pinjam::with('book')
            ->where('anggota_id', $anggotaId)
            ->where('status', 'dipinjam')
            ->get();
    }

    public function getBorrowingHistory(int $anggotaId)
    {
        return Pinjam::with(['book', 'pengembalian'])
            ->where('anggota_id', $anggotaId)
            ->latest()
            ->get();
    }

    public function getOverdueBorrowings()
    {
        return Pinjam::with(['anggota', 'book'])
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', Carbon::today())
            ->get();
    }

    public function getStats(): array
    {
        return [
            'total_books' => Book::count(),
            'active_loans' => Pinjam::where('status', 'dipinjam')->count(),
            'overdue' => Pinjam::where('status', 'dipinjam')
                ->whereDate('tanggal_kembali', '<', Carbon::today())
                ->count(),
            'today_returns' => Pinjam::where('status', 'dikembalikan')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
        ];
    }
}
