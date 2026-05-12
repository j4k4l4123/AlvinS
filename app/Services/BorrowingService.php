<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Book;
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
            $book = Book::findOrFail($bookId);
            $anggota = Anggota::with(['user.memberProfile', 'libraryCard'])->findOrFail($anggotaId);

            if (! $book->isAvailable()) {
                throw new \Exception('Buku "' . $book->judul . '" sedang dipinjam oleh anggota lain.');
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

            return Pinjam::create([
                'anggota_id' => $anggotaId,
                'book_id' => $bookId,
                'tanggal_pinjam' => $borrowDate->toDateString(),
                'tanggal_kembali' => $returnDate->toDateString(),
                'status' => 'dipinjam',
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
