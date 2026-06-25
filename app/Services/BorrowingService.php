<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Fine;
use App\Models\LibraryCard;
use App\Models\Pinjam;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BorrowingService
{
    public const MAX_BORROWINGS = 5;
    public const DEFAULT_BORROW_DAYS = 14;

    public function __construct(
        protected LibraryPolicyService $policyService,
        protected InventoryService $inventoryService,
        protected FineService $fineService,
    ) {}

    public function borrow(
        int $anggotaId,
        int $bookId,
        ?string $tanggalPinjam = null,
        ?string $tanggalKembali = null
    ): object {
        return DB::transaction(function () use ($anggotaId, $bookId, $tanggalPinjam, $tanggalKembali) {
            DB::table('book_reservations')
                ->where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            $book = Book::find($bookId);
            if (!$book) {
                throw new \Exception('Buku tidak ditemukan.');
            }

            $anggota = Anggota::find($anggotaId);
            if (!$anggota) {
                throw new \Exception('Anggota tidak ditemukan.');
            }
            $anggota->user = Anggota::userFor($anggota);
            if ($anggota->user) {
                $anggota->user->memberProfile = DB::table('member_profiles')->where('user_id', $anggota->user->id)->first();
            }
            $anggota->libraryCard = Anggota::libraryCardFor($anggota);

            $this->policyService->assertCanBorrow($anggota, $book);

            $activeReservation = DB::table('book_reservations')
                ->where('book_id', $book->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('expires_at', '>', now())
                ->orderBy('queue_position')
                ->first();

            if ($activeReservation && (int) $activeReservation->anggota_id !== (int) $anggotaId) {
                throw new \Exception('Buku ini sedang ada di antrean atau alokasi reservasi anggota lain.');
            }

            $user = $anggota->user;
            $memberProfile = $user?->memberProfile;

            if ($memberProfile && ($memberProfile->membership_status ?? 'active') !== 'active') {
                throw new \Exception('Status keanggotaan anggota tidak aktif. Tidak dapat meminjam buku.');
            }

            $borrowDate = $tanggalPinjam ? Carbon::parse($tanggalPinjam) : Carbon::today();
            $returnDate = $tanggalKembali
                ? Carbon::parse($tanggalKembali)
                : $borrowDate->copy()->addDays((int) ($book->max_loan_days ?: self::DEFAULT_BORROW_DAYS));

            $pinjamId = DB::table('pinjam')->insertGetId([
                'anggota_id' => $anggotaId,
                'book_id' => $bookId,
                'copy_code' => $this->inventoryService->generateCopyCode($book),
                'tanggal_pinjam' => $borrowDate->toDateString(),
                'tanggal_kembali' => $returnDate->toDateString(),
                'status' => 'dipinjam',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $pinjam = DB::table('pinjam')->where('id', $pinjamId)->first();

            if ($activeReservation && (int) $activeReservation->anggota_id === (int) $anggotaId) {
                DB::table('book_reservations')->where('id', $activeReservation->id)->update(['status' => 'completed']);
                $this->reindexReservationQueue($book->id);
            }

            $freshBook = DB::table('books')->where('id', $book->id)->first();
            if ($freshBook) {
                $this->inventoryService->refreshBookStatus($freshBook);
            }

            return $pinjam;
        });
    }

    public function borrowByBarcodes(string $cardNumber, string $bookBarcode): object
    {
        $card = DB::table('library_cards')->where('card_number', $cardNumber)->first();

        if (! $card) {
            throw new \Exception('Barcode kartu perpustakaan tidak ditemukan.');
        }

        if (! \App\Models\LibraryCard\LibraryCard::isActive($card)) {
            throw new \Exception('Kartu perpustakaan tidak aktif atau sudah kedaluwarsa.');
        }

        $book = DB::table('books')->where('id_buku', $bookBarcode)->orWhere('barcode', $bookBarcode)->first();

        if (! $book) {
            throw new \Exception('Barcode buku tidak ditemukan.');
        }

        return $this->borrow($card->anggota_id, $book->id);
    }

    public function reserve(int $anggotaId, int $bookId, int $userId): object
    {
        return DB::transaction(function () use ($anggotaId, $bookId, $userId) {
            DB::table('book_reservations')
                ->where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            $book = Book::find($bookId);
            if (!$book) {
                throw new \Exception('Buku tidak ditemukan.');
            }

            $anggota = Anggota::find($anggotaId);
            if (!$anggota) {
                throw new \Exception('Anggota tidak ditemukan.');
            }
            $anggota->user = Anggota::userFor($anggota);
            if ($anggota->user) {
                $anggota->user->memberProfile = DB::table('member_profiles')->where('user_id', $anggota->user->id)->first();
            }
            $anggota->libraryCard = Anggota::libraryCardFor($anggota);

            $this->policyService->assertCanReserve($anggota, $book);

            $user = $anggota->user;
            $memberProfile = $user?->memberProfile;

            if ($memberProfile && ($memberProfile->membership_status ?? 'active') !== 'active') {
                throw new \Exception('Status keanggotaan anggota tidak aktif. Tidak dapat melakukan reservasi.');
            }

            if ($anggota->libraryCard && ! \App\Models\LibraryCard\LibraryCard::isActive($anggota->libraryCard)) {
                throw new \Exception('Kartu perpustakaan anggota tidak aktif atau sudah kedaluwarsa.');
            }

            $existingOwn = DB::table('book_reservations')
                ->where('book_id', $bookId)
                ->where('anggota_id', $anggotaId)
                ->whereIn('status', ['pending', 'approved'])
                ->where('expires_at', '>', now())
                ->first();

            if ($existingOwn) {
                throw new \Exception('Kamu sudah ada di antrean reservasi buku ini.');
            }

            $queuePosition = ((int) DB::table('book_reservations')
                ->where('book_id', $bookId)
                ->whereIn('status', ['pending', 'approved'])
                ->max('queue_position')) + 1;

            $reservationId = DB::table('book_reservations')->insertGetId([
                'user_id' => $userId,
                'anggota_id' => $anggotaId,
                'book_id' => $bookId,
                'queue_position' => $queuePosition,
                'status' => 'pending',
                'expires_at' => now()->addDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $reservation = DB::table('book_reservations')->where('id', $reservationId)->first();

            $freshBook = DB::table('books')->where('id', $book->id)->first();
            if ($freshBook) {
                $this->inventoryService->refreshBookStatus($freshBook);
            }

            return $reservation;
        });
    }

    public function renew(object $pinjam): object
    {
        $this->policyService->assertCanRenew($pinjam);

        $book = Book::find($pinjam->book_id);
        $maxLoanDays = $book?->max_loan_days ?: self::DEFAULT_BORROW_DAYS;

        DB::table('pinjam')->where('id', $pinjam->id)->update([
            'tanggal_kembali' => Carbon::parse($pinjam->tanggal_kembali)->addDays((int) $maxLoanDays)->toDateString(),
            'renewal_count' => (int) ($pinjam->renewal_count ?? 0) + 1,
            'updated_at' => now(),
        ]);

        return DB::table('pinjam')->where('id', $pinjam->id)->first();
    }

    public function refreshBookInventory(object $book): void
    {
        $this->inventoryService->refreshBookStatus($book);
    }

    public function markLost(object $pinjam): void
    {
        $this->policyService->assertCanMarkLostOrDamaged($pinjam);

        DB::transaction(function () use ($pinjam) {
            DB::table('pinjam')->where('id', $pinjam->id)->update([
                'status' => 'hilang',
                'lost_at' => now(),
                'updated_at' => now(),
            ]);

            $book = Book::find($pinjam->book_id);
            if ($book) {
                $this->inventoryService->markAsLost($book);
            }
            $this->reindexReservationQueue((int) $pinjam->book_id);

            $fineExists = DB::table('fines')->where('pinjam_id', $pinjam->id)->exists();
            if ($fineExists) {
                DB::table('fines')->where('pinjam_id', $pinjam->id)->update([
                    'anggota_id' => $pinjam->anggota_id,
                    'amount' => $this->fineService->calculateLostBookFine($pinjam),
                    'type' => 'lost',
                    'status' => 'unpaid',
                    'notes' => 'Denda penggantian buku hilang.',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('fines')->insert([
                    'pinjam_id' => $pinjam->id,
                    'anggota_id' => $pinjam->anggota_id,
                    'amount' => $this->fineService->calculateLostBookFine($pinjam),
                    'type' => 'lost',
                    'status' => 'unpaid',
                    'notes' => 'Denda penggantian buku hilang.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function markDamaged(object $pinjam): void
    {
        $this->policyService->assertCanMarkLostOrDamaged($pinjam);

        DB::transaction(function () use ($pinjam) {
            DB::table('pinjam')->where('id', $pinjam->id)->update([
                'status' => 'rusak',
                'damaged_at' => now(),
                'updated_at' => now(),
            ]);

            $book = Book::find($pinjam->book_id);
            if ($book) {
                $this->inventoryService->markAsDamaged($book);
            }
            $this->reindexReservationQueue((int) $pinjam->book_id);

            $fineExists = DB::table('fines')->where('pinjam_id', $pinjam->id)->exists();
            if ($fineExists) {
                DB::table('fines')->where('pinjam_id', $pinjam->id)->update([
                    'anggota_id' => $pinjam->anggota_id,
                    'amount' => $this->fineService->calculateDamagedBookFine($pinjam),
                    'type' => 'damaged',
                    'status' => 'unpaid',
                    'notes' => 'Denda kerusakan buku.',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('fines')->insert([
                    'pinjam_id' => $pinjam->id,
                    'anggota_id' => $pinjam->anggota_id,
                    'amount' => $this->fineService->calculateDamagedBookFine($pinjam),
                    'type' => 'damaged',
                    'status' => 'unpaid',
                    'notes' => 'Denda kerusakan buku.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function cancel(int $pinjamId): void
    {
        DB::transaction(function () use ($pinjamId) {
            $pinjam = DB::table('pinjam')->where('id', $pinjamId)->first();
            if (!$pinjam) {
                throw new \Exception('Peminjaman tidak ditemukan.');
            }
            $bookId = (int) $pinjam->book_id;
            DB::table('pinjam')->where('id', $pinjam->id)->delete();

            if ($bookId) {
                $this->reindexReservationQueue($bookId);
                $book = Book::find($bookId);
                if ($book) {
                    $this->inventoryService->refreshBookStatus($book);
                }
            }
        });
    }

    public function reindexReservationQueue(int $bookId): void
    {
        $reservations = DB::table('book_reservations')
            ->where('book_id', $bookId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->orderByRaw("CASE WHEN status = 'approved' THEN 0 ELSE 1 END")
            ->orderBy('queue_position')
            ->orderBy('id')
            ->get();

        foreach ($reservations as $index => $reservation) {
            DB::table('book_reservations')->where('id', $reservation->id)->update(['queue_position' => $index + 1]);
        }
    }

    public function getActiveBorrowings(int $anggotaId)
    {
        $borrowings = DB::table('pinjam')
            ->where('anggota_id', $anggotaId)
            ->where('status', 'dipinjam')
            ->get();

        foreach ($borrowings as $b) {
            $b->book = Book::find($b->book_id);
            if ($b->tanggal_pinjam) {
                $b->tanggal_pinjam = Carbon::parse($b->tanggal_pinjam);
            }
            if ($b->tanggal_kembali) {
                $b->tanggal_kembali = Carbon::parse($b->tanggal_kembali);
            }
        }

        return $borrowings;
    }

    public function getBorrowingHistory(int $anggotaId)
    {
        $borrowings = DB::table('pinjam')
            ->where('anggota_id', $anggotaId)
            ->orderByDesc('id')
            ->get();

        foreach ($borrowings as $b) {
            $b->book = Book::find($b->book_id);
            $b->pengembalian = DB::table('pengembalian')->where('pinjam_id', $b->id)->first();
            if ($b->pengembalian && isset($b->pengembalian->tanggal_dikembalikan)) {
                $b->pengembalian->tanggal_dikembalikan = Carbon::parse($b->pengembalian->tanggal_dikembalikan);
            }
            if ($b->tanggal_pinjam) {
                $b->tanggal_pinjam = Carbon::parse($b->tanggal_pinjam);
            }
            if ($b->tanggal_kembali) {
                $b->tanggal_kembali = Carbon::parse($b->tanggal_kembali);
            }
        }

        return $borrowings;
    }

    public function getOverdueBorrowings()
    {
        $borrowings = DB::table('pinjam')
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', Carbon::today())
            ->get();

        foreach ($borrowings as $b) {
            $b->anggota = Anggota::find($b->anggota_id);
            $b->book = Book::find($b->book_id);
            if ($b->tanggal_pinjam) {
                $b->tanggal_pinjam = Carbon::parse($b->tanggal_pinjam);
            }
            if ($b->tanggal_kembali) {
                $b->tanggal_kembali = Carbon::parse($b->tanggal_kembali);
            }
        }

        return $borrowings;
    }

    public function getStats(): array
    {
        return [
            'total_books' => DB::table('books')->count(),
            'active_loans' => DB::table('pinjam')->where('status', 'dipinjam')->count(),
            'overdue' => DB::table('pinjam')->where('status', 'dipinjam')
                ->whereDate('tanggal_kembali', '<', Carbon::today())
                ->count(),
            'today_returns' => DB::table('pinjam')->where('status', 'dikembalikan')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
        ];
    }
}
