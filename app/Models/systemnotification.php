<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class SystemNotification
{
    protected const TABLE = 'notifications';

    public static function create(array $attributes): ?object
    {
        $data = $attributes;
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = now();
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = now();
        }

        $id = DB::table(static::TABLE)->insertGetId($data);
        return static::find($id);
    }

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function forUser(object|array $user): \Illuminate\Support\Collection
    {
        $userId = is_array($user) ? ($user['id'] ?? null) : ($user->id ?? null);
        if ($userId === null) {
            return collect();
        }

        return DB::table(static::TABLE)
            ->where('user_id', $userId)
            ->get();
    }

    public static function userFor(object|array $notification): ?object
    {
        $userId = is_array($notification) ? ($notification['user_id'] ?? null) : ($notification->user_id ?? null);
        if ($userId === null) {
            return null;
        }

        return DB::table('users')->where('id', $userId)->first();
    }
}

