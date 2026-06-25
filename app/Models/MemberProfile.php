<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class MemberProfile
{
    protected const TABLE = 'member_profiles';

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function userFor(object|array $profile): ?object
    {
        $userId = is_array($profile) ? ($profile['user_id'] ?? null) : ($profile->user_id ?? null);
        if ($userId === null) {
            return null;
        }

        return DB::table('users')->where('id', $userId)->first();
    }

    public static function isBanned(object|array $profile): bool
    {
        $status = is_array($profile) ? ($profile['membership_status'] ?? 'active') : ($profile->membership_status ?? 'active');
        return $status === 'banned';
    }

    public static function isExpired(object|array $profile): bool
    {
        $status = is_array($profile) ? ($profile['membership_status'] ?? 'active') : ($profile->membership_status ?? 'active');
        return $status === 'expired';
    }
}

