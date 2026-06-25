<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class LibrarianRegistrationRequest
{
    protected const TABLE = 'librarian_registration_requests';

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

    public static function all(): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)->get();
    }
}

