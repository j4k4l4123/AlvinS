<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Query-Builder based implementation for anggota.
 *
 * Note: This file intentionally does NOT use Eloquent ORM.
 * Methods return objects compatible with Eloquent expectations for views.
 */
class Anggota
{
    protected const TABLE = 'anggota';

    /**
     * Find a single anggota row by primary key.
     * Returns object with Eloquent-compatible properties.
     */
    public static function find(int|string $id): ?object
    {
        // Original model uses default PK; keep it as `id` as returned by DB.
        $row = DB::table(static::TABLE)->where('id', $id)->first();
        
        if ($row) {
            // Convert date fields to Carbon instances for Eloquent compatibility
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
            if (isset($row->tanggal_daftar)) {
                $row->tanggal_daftar = $row->tanggal_daftar instanceof Carbon 
                    ? $row->tanggal_daftar 
                    : Carbon::parse($row->tanggal_daftar);
            }
        }
        
        return $row;
    }

    /**
     * Search anggota by keyword (mirrors original scopeSearch).
     * Returns Collection with Eloquent-compatible objects.
     */
    public static function search(string $keyword): Collection
    {
        $kw = strtolower($keyword);

        $rows = DB::table(static::TABLE)
            ->where(function ($q) use ($kw) {
                $q->whereRaw('LOWER(nama) LIKE ?', ['%' . $kw . '%'])
                  ->orWhereRaw('LOWER(id_anggota) LIKE ?', ['%' . $kw . '%']);
            })
            ->get();

        // Convert date fields to Carbon instances for each item
        foreach ($rows as $row) {
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
            if (isset($row->tanggal_daftar)) {
                $row->tanggal_daftar = $row->tanggal_daftar instanceof Carbon 
                    ? $row->tanggal_daftar 
                    : Carbon::parse($row->tanggal_daftar);
            }
        }

        return $rows;
    }

    /**
     * Fetch related user for a given anggota.
     * Returns Eloquent User object for compatibility.
     */
    public static function userFor(object|array $anggota): ?object
    {
        $userId = is_array($anggota) ? ($anggota['user_id'] ?? null) : ($anggota->user_id ?? null);
        if ($userId === null) {
            return null;
        }

        // Return Eloquent User object instead of plain stdClass
        return \App\Models\User::find($userId);
    }

    /**
     * Fetch anggota's library card (original: hasOne with foreign key `anggota_id`).
     */
    public static function libraryCardFor(object|array $anggota): ?object
    {
        $anggotaId = is_array($anggota) ? ($anggota['id'] ?? null) : ($anggota->id ?? null);
        if ($anggotaId === null) {
            return null;
        }

        return DB::table('library_cards')
            ->where('anggota_id', $anggotaId)
            ->first();
    }

    /**
     * Active borrowings (original: pinjam()->where('status', 'dipinjam')).
     * Assumes table `pinjams` exists with fields: anggota_id, status.
     */
    public static function activeBorrowingsFor(object|array $anggota): Collection
    {
        $anggotaId = is_array($anggota) ? ($anggota['id'] ?? null) : ($anggota->id ?? null);
        if ($anggotaId === null) {
            return collect();
        }

        return DB::table('pinjam')
            ->where('anggota_id', $anggotaId)
            ->where('status', 'dipinjam')
            ->get();
    }

    public static function pinjamFor(object|array $anggota): Collection
    {
        $anggotaId = is_array($anggota) ? ($anggota['id'] ?? null) : ($anggota->id ?? null);
        if ($anggotaId === null) {
            return collect();
        }

        return DB::table('pinjam')->where('anggota_id', $anggotaId)->get();
    }

    public static function pengembalianFor(object|array $anggota): Collection
    {
        $anggotaId = is_array($anggota) ? ($anggota['id'] ?? null) : ($anggota->id ?? null);
        if ($anggotaId === null) {
            return collect();
        }

        return DB::table('pengembalian')->where('anggota_id', $anggotaId)->get();
    }

    public static function membershipRequestsFor(object|array $anggota): Collection
    {
        $anggotaId = is_array($anggota) ? ($anggota['id'] ?? null) : ($anggota->id ?? null);
        if ($anggotaId === null) {
            return collect();
        }

        return DB::table('membership_requests')->where('anggota_id', $anggotaId)->get();
    }

    public static function reservationsFor(object|array $anggota): Collection
    {
        $anggotaId = is_array($anggota) ? ($anggota['id'] ?? null) : ($anggota->id ?? null);
        if ($anggotaId === null) {
            return collect();
        }

        return DB::table('book_reservations')->where('anggota_id', $anggotaId)->get();
    }

    public static function renewalRequestsFor(object|array $anggota): Collection
    {
        $anggotaId = is_array($anggota) ? ($anggota['id'] ?? null) : ($anggota->id ?? null);
        if ($anggotaId === null) {
            return collect();
        }

        return DB::table('renewal_requests')->where('anggota_id', $anggotaId)->get();
    }
}

