<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class Role
{
    public const MEMBER = 'member';
    public const LIBRARIAN = 'librarian';
    public const ADMIN = 'admin';

    protected const TABLE = 'roles';
    protected const PIVOT_TABLE = 'role_user';

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function findByName(string $name): ?object
    {
        return DB::table(static::TABLE)->where('name', $name)->first();
    }

    public static function userIdsForRole(object|array $role): \Illuminate\Support\Collection
    {
        $roleId = is_array($role) ? ($role['id'] ?? null) : ($role->id ?? null);
        if ($roleId === null) {
            return collect();
        }

        return DB::table(static::PIVOT_TABLE)
            ->where('role_id', $roleId)
            ->pluck('user_id');
    }
}



