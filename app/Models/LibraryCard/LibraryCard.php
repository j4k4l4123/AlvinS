<?php

namespace App\Models\LibraryCard;

use Illuminate\Support\Facades\DB;

/**
 * Query-Builder based implementation for library cards.
 *
 * Note: This file intentionally does NOT use Eloquent ORM.
 */
class LibraryCard
{
    protected const TABLE = 'library_cards';

    /**
     * Find a single card by primary key.
     */
    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    /**
     * Get a card by card_number.
     */
    public static function findByCardNumber(string $cardNumber): ?object
    {
        return DB::table(static::TABLE)
            ->where('card_number', $cardNumber)
            ->first();
    }

    /**
     * Determine whether a given card row is active.
     *
     * Active rule (mirrors previous model logic):
     * - status === 'active'
     * - expiry_date is not null and is in the future
     */
    public static function isActive(object|array $card): bool
    {
        $status = is_array($card) ? ($card['status'] ?? null) : ($card->status ?? null);
        $expiry = is_array($card) ? ($card['expiry_date'] ?? null) : ($card->expiry_date ?? null);

        if ($status !== 'active' || $expiry === null) {
            return false;
        }

        // Let PHP compare using DateTime; avoids ORM casts.
        try {
            $expiryDate = new \DateTime($expiry);
            return $expiryDate > new \DateTime('now');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function active(): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', DB::raw('NOW()'))
            ->get();
    }

 
    public static function userFor(object|array $card): ?object
    {
        $userId = is_array($card) ? ($card['user_id'] ?? null) : ($card->user_id ?? null);
        if ($userId === null) {
            return null;
        }

        return DB::table('users')->where('id', $userId)->first();
    }


    public static function anggotaFor(object|array $card): ?object
    {
        $anggotaId = is_array($card) ? ($card['anggota_id'] ?? null) : ($card->anggota_id ?? null);
        if ($anggotaId === null) {
            return null;
        }

        return DB::table('anggotas')->where('id', $anggotaId)->first();
    }
}

