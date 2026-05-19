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
    public const FINE_PER_DAY = 5000;

    public function calculateFine(string $tanggalKembali, string $tanggalDikembalikan): int
    {
        $dueDate = Carbon::parse($tanggalKembali)->startOfDay();
        $returnDate = Carbon::parse($tanggalDikembalikan)->startOfDay();

        $daysLate = max(0, $dueDate->diffInDays($returnDate, false));

        return (int) ($daysLate * self::FINE_PER_DAY);
    }

    public function processReturn(int $pinjamId, string $tanggalDikembalikan): Pengembalian
    {
        return DB::transaction(function () use ($pinjamId, $tanggalDikembalikan) {
            $pinjam = Pinjam::with(['book', 'anggota.user'])->findOrFail($pinjamId);

            $denda = $this->calculateFine((string) $pinjam->tanggal_kembali, $tanggalDikembalikan);

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

            if ($denda > 0) {
                Fine::updateOrCreate(
                    ['pinjam_id' => $pinjam->id],
                    [
                        'pengembalian_id' => $pengembalian->id,
                        'anggota_id' => $pinjam->anggota_id,
                        'amount' => $denda,
                        'status' => 'unpaid',
                        'notes' => 'Denda otomatis karena terlambat mengembalikan buku.',
                    ]
                );
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
                    'fine' => $daysLate * self::FINE_PER_DAY,
                ];
            });
    }
}
