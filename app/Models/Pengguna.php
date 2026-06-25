<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class Pengguna
{
    protected const TABLE = 'pengguna';

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function findByEmail(string $email): ?object
    {
        return DB::table(static::TABLE)->where('email', $email)->first();
    }
}

