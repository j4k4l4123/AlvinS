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
    ): Pinjam {
        return DB::transaction(function () use ($anggotaId, $bookId, $tanggalPinjam, $tanggalKembali) {
            BookReservation::where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            $book = Book::findOrFail($bookId);
            $anggota = Anggota::with(['user.memberProfile', 'libraryCard'])->findOrFail($anggotaId);

            $this->policyService->assertCanBorrow($anggota, $book);

            $activeReservation = BookReservation::where('book_id', $book->id)
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

            $pinjam = Pinjam::create([
                'anggota_id' => $anggotaId,
                'book_id' => $bookId,
                'copy_code' => $this->inventoryService->generateCopyCode($book),
                'tanggal_pinjam' => $borrowDate->toDateString(),
                'tanggal_kembali' => $returnDate->toDateString(),
                'status' => 'dipinjam',
            ]);

            if ($activeReservation && (int) $activeReservation->anggota_id === (int) $anggotaId) {
                $activeReservation->update(['status' => 'completed']);
                $this->reindexReservationQueue($book->id);
            }

            $this->inventoryService->refreshBookStatus($book->fresh());

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

        $book = Book::where('id_buku', $bookBarcode)->orWhere('barcode', $bookBarcode)->first();

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

            $this->policyService->assertCanReserve($anggota, $book);

            $user = $anggota->user;
            $memberProfile = $user?->memberProfile;

            if ($memberProfile && ($memberProfile->membership_status ?? 'active') !== 'active') {
                throw new \Exception('Status keanggotaan anggota tidak aktif. Tidak dapat melakukan reservasi.');
            }

            if ($anggota->libraryCard && ! $anggota->libraryCard->isActive()) {
                throw new \Exception('Kartu perpustakaan anggota tidak aktif atau sudah kedaluwarsa.');
            }

            $existingOwn = BookReservation::where('book_id', $bookId)
                ->where('anggota_id', $anggotaId)
                ->whereIn('status', ['pending', 'approved'])
                ->where('expires_at', '>', now())
                ->first();

            if ($existingOwn) {
                throw new \Exception('Kamu sudah ada di antrean reservasi buku ini.');
            }

            $queuePosition = ((int) BookReservation::where('book_id', $bookId)
                ->whereIn('status', ['pending', 'approved'])
                ->max('queue_position')) + 1;

            $reservation = BookReservation::create([
                'user_id' => $userId,
                'anggota_id' => $anggotaId,
                'book_id' => $bookId,
                'queue_position' => $queuePosition,
                'status' => 'pending',
                'expires_at' => now()->addDay(),
            ]);

            $this->inventoryService->refreshBookStatus($book->fresh());

            return $reservation;
        });
    }

    public function renew(Pinjam $pinjam): Pinjam
    {
        $this->policyService->assertCanRenew($pinjam);

        $pinjam->update([
            'tanggal_kembali' => $pinjam->tanggal_kembali->copy()->addDays((int) ($pinjam->book?->max_loan_days ?: self::DEFAULT_BORROW_DAYS)),
            'renewal_count' => (int) ($pinjam->renewal_count ?? 0) + 1,
        ]);

        return $pinjam->fresh();
    }

    public function refreshBookInventory(Book $book): void
    {
        $this->inventoryService->refreshBookStatus($book);
    }

    public function markLost(Pinjam $pinjam): void
    {
        $this->policyService->assertCanMarkLostOrDamaged($pinjam);

        DB::transaction(function () use ($pinjam) {
            $pinjam->update([
                'status' => 'hilang',
                'lost_at' => now(),
            ]);

            $this->inventoryService->markAsLost($pinjam->book);
            $this->reindexReservationQueue((int) $pinjam->book_id);

            Fine::updateOrCreate(
                ['pinjam_id' => $pinjam->id],
                [
                    'anggota_id' => $pinjam->anggota_id,
                    'amount' => $this->fineService->calculateLostBookFine($pinjam),
                    'type' => 'lost',
                    'status' => 'unpaid',
                    'notes' => 'Denda penggantian buku hilang.',
                ]
            );
        });
    }

    public function markDamaged(Pinjam $pinjam): void
    {
        $this->policyService->assertCanMarkLostOrDamaged($pinjam);

        DB::transaction(function () use ($pinjam) {
            $pinjam->update([
                'status' => 'rusak',
                'damaged_at' => now(),
            ]);

            $this->inventoryService->markAsDamaged($pinjam->book);
            $this->reindexReservationQueue((int) $pinjam->book_id);

            Fine::updateOrCreate(
                ['pinjam_id' => $pinjam->id],
                [
                    'anggota_id' => $pinjam->anggota_id,
                    'amount' => $this->fineService->calculateDamagedBookFine($pinjam),
                    'type' => 'damaged',
                    'status' => 'unpaid',
                    'notes' => 'Denda kerusakan buku.',
                ]
            );
        });
    }

    public function cancel(int $pinjamId): void
    {
        DB::transaction(function () use ($pinjamId) {
            $pinjam = Pinjam::findOrFail($pinjamId);
            $bookId = (int) $pinjam->book_id;
            $pinjam->delete();

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
        $reservations = BookReservation::where('book_id', $bookId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->orderByRaw("CASE WHEN status = 'approved' THEN 0 ELSE 1 END")
            ->orderBy('queue_position')
            ->orderBy('id')
            ->get();

        foreach ($reservations as $index => $reservation) {
            $reservation->update(['queue_position' => $index + 1]);
        }
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
