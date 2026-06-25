<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class RenewalRequest
{
    protected const TABLE = 'renewal_requests';

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function userFor(object|array $request): ?object
    {
        $userId = is_array($request) ? ($request['user_id'] ?? null) : ($request->user_id ?? null);
        if ($userId === null) {
            return null;
        }

        return DB::table('users')->where('id', $userId)->first();
    }

    public static function anggotaFor(object|array $request): ?object
    {
        $anggotaId = is_array($request) ? ($request['anggota_id'] ?? null) : ($request->anggota_id ?? null);
        if ($anggotaId === null) {
            return null;
        }

        return DB::table('anggota')->where('id', $anggotaId)->first();
    }

    public static function borrowingFor(object|array $request): ?object
    {
        $pinjamId = is_array($request) ? ($request['pinjam_id'] ?? null) : ($request->pinjam_id ?? null);
        if ($pinjamId === null) {
            return null;
        }

        return DB::table('pinjam')->where('id', $pinjamId)->first();
    }

    public static function processedByFor(object|array $request): ?object
    {
        $processedById = is_array($request)
            ? ($request['processed_by'] ?? null)
            : ($request->processed_by ?? null);

        if ($processedById === null) {
            return null;
        }

        return DB::table('users')->where('id', $processedById)->first();
    }
}

