<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\Pengembalian;
use App\Models\Pinjam;
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

    public function calculateFine(string $tanggalKembali, string $tanggalDikembalikan, ?Pinjam $pinjam = null): int
    {
        $dueDate = Carbon::parse($tanggalKembali)->startOfDay();
        $returnDate = Carbon::parse($tanggalDikembalikan)->startOfDay();

        $daysLate = max(0, $dueDate->diffInDays($returnDate, false));
        $dailyRate = (int) round((float) ($pinjam?->book?->daily_late_fee ?? self::FINE_PER_DAY));

        return (int) ($daysLate * $dailyRate);
    }

    public function calculateLostBookFine(Pinjam $pinjam): int
    {
        return (int) round((float) ($pinjam->book?->price ?? 0) * self::LOST_BOOK_MULTIPLIER);
    }

    public function calculateDamagedBookFine(Pinjam $pinjam): int
    {
        return (int) round((float) ($pinjam->book?->price ?? 0) * self::DAMAGED_BOOK_PERCENTAGE);
    }

    public function processReturn(int $pinjamId, string $tanggalDikembalikan): Pengembalian
    {
        return DB::transaction(function () use ($pinjamId, $tanggalDikembalikan) {
            $pinjam = Pinjam::with(['book', 'anggota.user'])->findOrFail($pinjamId);

            $denda = $this->calculateFine((string) $pinjam->tanggal_kembali, $tanggalDikembalikan, $pinjam);

            $pengembalian = Pengembalian::create([
                'pinjam_id' => $pinjam->id,
                'anggota_id' => $pinjam->anggota_id,
                'book_id' => $pinjam->book_id,
                'tanggal_pinjam' => $pinjam->tanggal_pinjam,
                'tanggal_kembali' => $pinjam->tanggal_kembali,
                'tanggal_dikembalikan' => $tanggalDikembalikan,
                'denda' => $denda,
            ]);

            $pinjam->update(['status' => 'dikembalikan']);
            if ($pinjam->book) {
                $this->inventoryService->refreshBookStatus($pinjam->book->fresh());
            }

            if ($denda > 0) {
                Fine::updateOrCreate(
                    ['pinjam_id' => $pinjam->id],
                    [
                        'pengembalian_id' => $pengembalian->id,
                        'anggota_id' => $pinjam->anggota_id,
                        'amount' => $denda,
                        'type' => 'late',
                        'status' => 'unpaid',
                        'notes' => 'Denda otomatis karena terlambat mengembalikan buku.',
                    ]
                );
            } else {
                Fine::where('pinjam_id', $pinjam->id)
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

    public function markFineAsPaid(Pinjam $pinjam): void
    {
        $pinjam->fine()?->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function getTotalFines(int $anggotaId): int
    {
        return Fine::where('anggota_id', $anggotaId)
            ->where('status', 'unpaid')
            ->sum('amount');
    }

    public function getOverdueFinesSummary()
    {
        return Pinjam::with(['anggota', 'book'])
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', Carbon::today())
            ->get()
            ->map(function ($pinjam) {
                $daysLate = max(0, Carbon::parse($pinjam->tanggal_kembali)->diffInDays(Carbon::today(), false));

                return [
                    'pinjam' => $pinjam,
                    'days_late' => $daysLate,
                    'fine' => $daysLate * (int) round((float) ($pinjam->book?->daily_late_fee ?? self::FINE_PER_DAY)),
                ];
            });
    }
}
