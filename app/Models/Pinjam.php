<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class Pinjam
{
    protected const TABLE = 'pinjam';

    public static function find(int|string $id): ?object
    {
        $row = DB::table(static::TABLE)->where('id', $id)->first();
        if ($row) {
            if (isset($row->tanggal_pinjam)) {
                $row->tanggal_pinjam = $row->tanggal_pinjam instanceof Carbon 
                    ? $row->tanggal_pinjam 
                    : Carbon::parse($row->tanggal_pinjam);
            }
            if (isset($row->tanggal_kembali)) {
                $row->tanggal_kembali = $row->tanggal_kembali instanceof Carbon 
                    ? $row->tanggal_kembali 
                    : Carbon::parse($row->tanggal_kembali);
            }
            if (isset($row->created_at)) {
                $row->created_at = $row->created_at instanceof Carbon 
                    ? $row->created_at 
                    : Carbon::parse($row->created_at);
            }
            if (isset($row->updated_at)) {
                $row->updated_at = $row->updated_at instanceof Carbon 
                    ? $row->updated_at 
                    : Carbon::parse($row->updated_at);
            }

            if (isset($row->anggota_id)) {
                $row->anggota = Anggota::find($row->anggota_id);
            }
            if (isset($row->book_id)) {
                $row->book = Book::find($row->book_id);
            }
        }
        return $row;
    }

    public static function forAnggota(int $anggotaId): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)
            ->where('anggota_id', $anggotaId)
            ->get();
    }

    public static function forBook(int $bookId): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)
            ->where('book_id', $bookId)
            ->get();
    }

    public static function active(): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)
            ->where('status', 'dipinjam')
            ->get();
    }

    public static function overdue(): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)
            ->where('status', 'dipinjam')
            ->whereDate('tanggal_kembali', '<', Carbon::today())
            ->get();
    }

    public static function isOverdue(object|array $pinjam): bool
    {
        $status = is_array($pinjam) ? ($pinjam['status'] ?? null) : ($pinjam->status ?? null);
        $tanggalKembali = is_array($pinjam) ? ($pinjam['tanggal_kembali'] ?? null) : ($pinjam->tanggal_kembali ?? null);

        if ($status !== 'dipinjam' || empty($tanggalKembali)) {
            return false;
        }

        try {
            $kembali = Carbon::parse($tanggalKembali)->startOfDay();
            return Carbon::today()->gt($kembali);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function daysOverdue(object|array $pinjam): int
    {
        if (! static::isOverdue($pinjam)) {
            return 0;
        }

        $tanggalKembali = is_array($pinjam) ? ($pinjam['tanggal_kembali'] ?? null) : ($pinjam->tanggal_kembali ?? null);

        try {
            $kembali = Carbon::parse($tanggalKembali)->startOfDay();
            return Carbon::today()->diffInDays($kembali);
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function calculateFine(object|array $pinjam): int
    {
        if (! static::isOverdue($pinjam)) {
            return 0;
        }

        $bookId = is_array($pinjam) ? ($pinjam['book_id'] ?? null) : ($pinjam->book_id ?? null);
        if ($bookId === null) {
            return static::daysOverdue($pinjam) * 5000;
        }

        $feeRow = DB::table('books')->where('id', $bookId)->select('daily_late_fee')->first();
        $dailyFee = $feeRow?->daily_late_fee ?? 5000;

        return static::daysOverdue($pinjam) * (int) round((float) $dailyFee);
    }

    public static function pengembalianFor(object|array $pinjam): ?object
    {
        $pinjamId = is_array($pinjam) ? ($pinjam['id'] ?? null) : ($pinjam->id ?? null);
        if ($pinjamId === null) {
            return null;
        }

        return DB::table('pengembalians')->where('pinjam_id', $pinjamId)->first();
    }

    public static function fineFor(object|array $pinjam): ?object
    {
        $pinjamId = is_array($pinjam) ? ($pinjam['id'] ?? null) : ($pinjam->id ?? null);
        if ($pinjamId === null) {
            return null;
        }

        return DB::table('fines')->where('pinjam_id', $pinjamId)->first();
    }
}

