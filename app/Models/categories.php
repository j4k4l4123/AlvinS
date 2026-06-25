<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class categories
{
    protected const TABLE = 'categories';

   
    public static function find(int|string $categoryId): ?object
    {
        return DB::table(static::TABLE)
            ->where('category_id', $categoryId)
            ->first();
    }


    public static function all(): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)->get();
    }

  
    public static function bukuFor(object|array $category): \Illuminate\Support\Collection
    {
        $categoryId = is_array($category)
            ? ($category['category_id'] ?? null)
            : ($category->category_id ?? null);

        if ($categoryId === null) {
            return collect();
        }

        return DB::table('books')
            ->where('category_id', $categoryId)
            ->get();
    }
}

