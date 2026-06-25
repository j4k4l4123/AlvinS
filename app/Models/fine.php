<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class Fine
{
    protected const TABLE = 'fines';

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function forAnggota(object|array $anggota): \Illuminate\Support\Collection
    {
        $anggotaId = is_array($anggota) ? ($anggota['id'] ?? null) : ($anggota->id ?? null);

        if ($anggotaId === null) {
            return collect();
        }

        return DB::table(static::TABLE)
            ->where('anggota_id', $anggotaId)
            ->get();
    }

    public static function peminjamanFor(object|array $fine): ?object
    {
        $pinjamId = is_array($fine) ? ($fine['pinjam_id'] ?? null) : ($fine->pinjam_id ?? null);
        if ($pinjamId === null) {
            return null;
        }

        return DB::table('pinjam')->where('id', $pinjamId)->first();
    }

    public static function pengembalianFor(object|array $fine): ?object
    {
        $pengembalianId = is_array($fine) ? ($fine['pengembalian_id'] ?? null) : ($fine->pengembalian_id ?? null);
        if ($pengembalianId === null) {
            return null;
        }

        return DB::table('pengembalian')->where('id', $pengembalianId)->first();
    }

    public static function anggotaFor(object|array $fine): ?object
    {
        $anggotaId = is_array($fine) ? ($fine['anggota_id'] ?? null) : ($fine->anggota_id ?? null);
        if ($anggotaId === null) {
            return null;
        }

        return DB::table('anggota')->where('id', $anggotaId)->first();
    }
}

