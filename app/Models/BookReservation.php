<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Query-Builder based implementation for book reservations.
 *
 * Note: This file intentionally does NOT use Eloquent ORM.
 */
class BookReservation
{
    protected const TABLE = 'book_reservations';

    public static function find(int|string $id): ?object
    {
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    /**
     * Mirrors previous model logic (casts + relationships removed).
     */
    public static function isActive(object|array $reservation): bool
    {
        $status = is_array($reservation) ? ($reservation['status'] ?? null) : ($reservation->status ?? null);
        $expiresAt = is_array($reservation) ? ($reservation['expires_at'] ?? null) : ($reservation->expires_at ?? null);

        if (!in_array($status, ['pending', 'approved'], true)) {
            return false;
        }

        if ($expiresAt === null || $expiresAt === '') {
            return false;
        }

        try {
            return (new \DateTime($expiresAt)) > new \DateTime('now');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function isApproved(object|array $reservation): bool
    {
        $status = is_array($reservation) ? ($reservation['status'] ?? null) : ($reservation->status ?? null);
        $expiresAt = is_array($reservation) ? ($reservation['expires_at'] ?? null) : ($reservation->expires_at ?? null);

        if ($status !== 'approved') {
            return false;
        }

        if ($expiresAt === null || $expiresAt === '') {
            return false;
        }

        try {
            return (new \DateTime($expiresAt)) > new \DateTime('now');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function userFor(object|array $reservation): ?object
    {
        $userId = is_array($reservation) ? ($reservation['user_id'] ?? null) : ($reservation->user_id ?? null);
        if ($userId === null) {
            return null;
        }

        return DB::table('users')->where('id', $userId)->first();
    }

    public static function anggotaFor(object|array $reservation): ?object
    {
        $anggotaId = is_array($reservation) ? ($reservation['anggota_id'] ?? null) : ($reservation->anggota_id ?? null);
        if ($anggotaId === null) {
            return null;
        }

        return DB::table('anggota')->where('id', $anggotaId)->first();
    }

    public static function bukuFor(object|array $reservation): ?object
    {
        $bookId = is_array($reservation) ? ($reservation['book_id'] ?? null) : ($reservation->book_id ?? null);
        if ($bookId === null) {
            return null;
        }

        return DB::table('books')->where('id', $bookId)->first();
    }
}

