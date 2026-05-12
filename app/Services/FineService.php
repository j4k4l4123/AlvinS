<?php

namespace App\Services;

use App\Models\Pengembalian;
use App\Models\Pinjam;
use Carbon\Carbon;

class FineService
{
    public const FINE_PER_DAY = 5000;

    public function calculateFine(string $tanggalKembali, string $tanggalDikembalikan): int
    {
        $dueDate = Carbon::parse($tanggalKembali)->startOfDay();
        $returnDate = Carbon::parse($tanggalDikembalikan)->startOfDay();

        $daysLate = max(0, $returnDate->diffInDays($dueDate, false));

        return (int) ($daysLate * self::FINE_PER_DAY);
    }

    public function processReturn(int $pinjamId, string $tanggalDikembalikan): Pengembalian
    {
        $pinjam = Pinjam::findOrFail($pinjamId);

        $denda = $this->calculateFine($pinjam->tanggal_kembali, $tanggalDikembalikan);

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

        return $pengembalian;
    }

    public function getTotalFines(int $anggotaId): int
    {
        return Pengembalian::where('anggota_id', $anggotaId)
            ->where('denda', '>', 0)
            ->sum('denda');
    }

    public function getOverdueFinesSummary()
    {
        return Pinjam::with(['anggota', 'book'])
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', Carbon::today())
            ->get()
            ->map(function ($pinjam) {
                $daysLate = Carbon::today()->diffInDays(Carbon::parse($pinjam->tanggal_kembali), false);
                $daysLate = max(0, $daysLate);

                return [
                    'pinjam' => $pinjam,
                    'days_late' => $daysLate,
                    'fine' => $daysLate * self::FINE_PER_DAY,
                ];
            });
    }
}
