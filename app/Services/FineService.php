<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\Pengembalian;
use App\Models\Pinjam;
use App\Models\Anggota;
use App\Models\Book;
use App\Support\NotificationHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FineService
{
    public function __construct(protected InventoryService $inventoryService)
    {
    }

    public const FINE_PER_DAY = 5000;
    public const LOST_BOOK_MULTIPLIER = 1;
    public const DAMAGED_BOOK_PERCENTAGE = 0.3;

    public function calculateFine(string $tanggalKembali, string $tanggalDikembalikan, ?object $pinjam = null): int
    {
        $dueDate = Carbon::parse($tanggalKembali)->startOfDay();
        $returnDate = Carbon::parse($tanggalDikembalikan)->startOfDay();

        $daysLate = max(0, $dueDate->diffInDays($returnDate, false));
        
        $book = $pinjam ? Book::find($pinjam->book_id) : null;
        $dailyRate = (int) round((float) ($book?->daily_late_fee ?? self::FINE_PER_DAY));

        return (int) ($daysLate * $dailyRate);
    }

    public function calculateLostBookFine(object $pinjam): int
    {
        $book = Book::find($pinjam->book_id);
        return (int) round((float) ($book?->price ?? 0) * self::LOST_BOOK_MULTIPLIER);
    }

    public function calculateDamagedBookFine(object $pinjam): int
    {
        $book = Book::find($pinjam->book_id);
        return (int) round((float) ($book?->price ?? 0) * self::DAMAGED_BOOK_PERCENTAGE);
    }

    public function processReturn(int $pinjamId, string $tanggalDikembalikan): object
    {
        return DB::transaction(function () use ($pinjamId, $tanggalDikembalikan) {
            $pinjam = DB::table('pinjam')->where('id', $pinjamId)->first();
            if (!$pinjam) {
                throw new \Exception('Peminjaman tidak ditemukan.');
            }

            $pinjam->book = Book::find($pinjam->book_id);
            $pinjam->anggota = Anggota::find($pinjam->anggota_id);
            if ($pinjam->anggota) {
                $pinjam->anggota->user = Anggota::userFor($pinjam->anggota);
            }

            $denda = $this->calculateFine((string) $pinjam->tanggal_kembali, $tanggalDikembalikan, $pinjam);

            $pengembalianId = DB::table('pengembalian')->insertGetId([
                'pinjam_id' => $pinjam->id,
                'anggota_id' => $pinjam->anggota_id,
                'book_id' => $pinjam->book_id,
                'tanggal_pinjam' => $pinjam->tanggal_pinjam,
                'tanggal_kembali' => $pinjam->tanggal_kembali,
                'tanggal_dikembalikan' => $tanggalDikembalikan,
                'denda' => $denda,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $pengembalian = DB::table('pengembalian')->where('id', $pengembalianId)->first();

            DB::table('pinjam')->where('id', $pinjam->id)->update([
                'status' => 'dikembalikan',
                'updated_at' => now(),
            ]);

            if ($pinjam->book) {
                $freshBook = DB::table('books')->where('id', $pinjam->book->id)->first();
                if ($freshBook) {
                    $this->inventoryService->refreshBookStatus($freshBook);
                }
            }

            if ($denda > 0) {
                $fineExists = DB::table('fines')->where('pinjam_id', $pinjam->id)->exists();
                if ($fineExists) {
                    DB::table('fines')->where('pinjam_id', $pinjam->id)->update([
                        'pengembalian_id' => $pengembalian->id,
                        'anggota_id' => $pinjam->anggota_id,
                        'amount' => $denda,
                        'type' => 'late',
                        'status' => 'unpaid',
                        'notes' => 'Denda otomatis karena terlambat mengembalikan buku.',
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('fines')->insert([
                        'pinjam_id' => $pinjam->id,
                        'pengembalian_id' => $pengembalian->id,
                        'anggota_id' => $pinjam->anggota_id,
                        'amount' => $denda,
                        'type' => 'late',
                        'status' => 'unpaid',
                        'notes' => 'Denda otomatis karena terlambat mengembalikan buku.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                DB::table('fines')
                    ->where('pinjam_id', $pinjam->id)
                    ->where('type', 'late')
                    ->delete();
            }

            if ($pinjam->anggota?->user_id) {
                $message = 'Buku "' . ($pinjam->book?->judul ?? '-') . '" berhasil dikembalikan.';
                if ($denda > 0) {
                    $message .= ' Kamu terkena denda Rp ' . number_format($denda, 0, ',', '.') . '.';
                }

                NotificationHelper::send(
                    $pinjam->anggota->user_id,
                    'book_returned',
                    'Pengembalian buku berhasil',
                    $message,
                    [
                        'pinjam_id' => $pinjam->id,
                        'pengembalian_id' => $pengembalian->id,
                        'denda' => $denda,
                    ]
                );
            }

            return $pengembalian;
        });
    }

    public function markFineAsPaid(object $pinjam): void
    {
        DB::table('fines')
            ->where('pinjam_id', $pinjam->id)
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function getTotalFines(int $anggotaId): int
    {
        return (int) DB::table('fines')
            ->where('anggota_id', $anggotaId)
            ->where('status', 'unpaid')
            ->sum('amount');
    }

    public function getOverdueFinesSummary()
    {
        $borrowings = DB::table('pinjam')
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', Carbon::today())
            ->get();

        foreach ($borrowings as $pinjam) {
            $pinjam->anggota = Anggota::find($pinjam->anggota_id);
            $pinjam->book = Book::find($pinjam->book_id);
        }

        return $borrowings->map(function ($pinjam) {
            $daysLate = max(0, Carbon::parse($pinjam->tanggal_kembali)->diffInDays(Carbon::today(), false));

            return [
                'pinjam' => $pinjam,
                'days_late' => $daysLate,
                'fine' => $daysLate * (int) round((float) ($pinjam->book?->daily_late_fee ?? self::FINE_PER_DAY)),
            ];
        });
    }
}
