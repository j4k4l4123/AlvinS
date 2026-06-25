<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class MembershipRequest
{
    protected const TABLE = 'membership_requests';

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
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

