<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Query-Builder based implementation for authors.
 *
 * Note: This file intentionally does NOT use Eloquent ORM.
 */
class authors
{
    protected const TABLE = 'authors';

    /**
     * Find author by primary key.
     */
    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function all(): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)->get();
    }

   
    public static function bukuFor(object|array $author): \Illuminate\Support\Collection
    {
        $authorId = is_array($author) ? ($author['id'] ?? null) : ($author->id ?? null);
        if ($authorId === null) {
            return collect();
        }

        return DB::table('books')
            ->where('author_id', $authorId)
            ->get();
    }
}

