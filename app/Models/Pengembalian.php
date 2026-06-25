<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class Pengembalian
{
    protected const TABLE = 'pengembalian';

    public static function find(int|string $id): ?object
    {
        $row = DB::table(static::TABLE)->where('id', $id)->first();
        if ($row) {
            if (isset($row->tanggal_pinjam) && is_string($row->tanggal_pinjam)) {
                $row->tanggal_pinjam = \Carbon\Carbon::parse($row->tanggal_pinjam);
            }
            if (isset($row->tanggal_kembali) && is_string($row->tanggal_kembali)) {
                $row->tanggal_kembali = \Carbon\Carbon::parse($row->tanggal_kembali);
            }
            if (isset($row->tanggal_dikembalikan) && is_string($row->tanggal_dikembalikan)) {
                $row->tanggal_dikembalikan = \Carbon\Carbon::parse($row->tanggal_dikembalikan);
            }
            if (isset($row->created_at) && is_string($row->created_at)) {
                $row->created_at = \Carbon\Carbon::parse($row->created_at);
            }
            if (isset($row->updated_at) && is_string($row->updated_at)) {
                $row->updated_at = \Carbon\Carbon::parse($row->updated_at);
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

    public static function withDenda(): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)
            ->where('denda', '>', 0)
            ->get();
    }

    public static function pinjamFor(object|array $pengembalian): ?object
    {
        $pinjamId = is_array($pengembalian) ? ($pengembalian['pinjam_id'] ?? null) : ($pengembalian->pinjam_id ?? null);
        if ($pinjamId === null) {
            return null;
        }

        return DB::table('pinjam')->where('id', $pinjamId)->first();
    }

    public static function anggotaFor(object|array $pengembalian): ?object
    {
        $anggotaId = is_array($pengembalian) ? ($pengembalian['anggota_id'] ?? null) : ($pengembalian->anggota_id ?? null);
        if ($anggotaId === null) {
            return null;
        }

        return DB::table('anggota')->where('id', $anggotaId)->first();
    }

    public static function bookFor(object|array $pengembalian): ?object
    {
        $bookId = is_array($pengembalian) ? ($pengembalian['book_id'] ?? null) : ($pengembalian->book_id ?? null);
        if ($bookId === null) {
            return null;
        }

        return DB::table('books')->where('id', $bookId)->first();
    }

    public static function fineFor(object|array $pengembalian): ?object
    {
        $pengembalianId = is_array($pengembalian)
            ? ($pengembalian['id'] ?? null)
            : ($pengembalian->id ?? null);

        if ($pengembalianId === null) {
            return null;
        }

        return DB::table('fines')
            ->where('pengembalian_id', $pengembalianId)
            ->first();
    }
}

