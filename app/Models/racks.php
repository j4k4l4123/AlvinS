<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class racks
{
    protected const TABLE = 'racks';

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function bukuFor(object|array $rack): \Illuminate\Support\Collection
    {
        $rackId = is_array($rack) ? ($rack['id'] ?? null) : ($rack->id ?? null);
        if ($rackId === null) {
            return collect();
        }

        return DB::table('books')
            ->where('rack_id', $rackId)
            ->get();
    }

    public static function totalBukuFor(object|array $rack): int
    {
        $rackId = is_array($rack) ? ($rack['id'] ?? null) : ($rack->id ?? null);
        if ($rackId === null) {
            return 0;
        }

        return (int) DB::table('books')
            ->where('rack_id', $rackId)
            ->sum('stock');
    }

    // Backward compatibility (views may call totalBooks())
    public static function totalBooksFor(object|array $rack): int
    {
        return static::totalBukuFor($rack);
    }
}

