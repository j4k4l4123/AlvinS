<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Book
{
    protected const TABLE = 'books';

    public static function find(int|string $id): ?object
    {
        // Most of your other Query-Builder models use `id` as PK.
        return DB::table(static::TABLE)->where('id', $id)->first();
    }

    public static function all(): \Illuminate\Support\Collection
    {
        return DB::table(static::TABLE)->get();
    }

    public static function penulisFor(object|array $book): ?object
    {
        $authorId = is_array($book) ? ($book['author_id'] ?? null) : ($book->author_id ?? null);
        if ($authorId === null) {
            return null;
        }

        return DB::table('authors')->where('id', $authorId)->first();
    }

    public static function kategoriFor(object|array $book): ?object
    {
        $categoryId = is_array($book) ? ($book['category_id'] ?? null) : ($book->category_id ?? null);
        if ($categoryId === null) {
            return null;
        }

        return DB::table('categories')->where('category_id', $categoryId)->first();
    }

    public static function rackFor(object|array $book): ?object
    {
        $rackId = is_array($book) ? ($book['rack_id'] ?? null) : ($book->rack_id ?? null);
        if ($rackId === null) {
            return null;
        }

        return DB::table('racks')->where('id', $rackId)->first();
    }

    // Backward compatibility for old method name used in views/components.
    public static function rakFor(object|array $book): ?object
    {
        return static::rackFor($book);
    }

    public static function pinjamFor(object|array $book): \Illuminate\Support\Collection
    {
        $bookId = is_array($book) ? ($book['id'] ?? null) : ($book->id ?? null);
        if ($bookId === null) {
            return collect();
        }

        return DB::table('pinjam')->where('book_id', $bookId)->get();
    }

    public static function reservationsFor(object|array $book): \Illuminate\Support\Collection
    {
        $bookId = is_array($book) ? ($book['id'] ?? null) : ($book->id ?? null);
        if ($bookId === null) {
            return collect();
        }

        return DB::table('book_reservations')->where('book_id', $bookId)->get();
    }

    // Backward compatibility for old name
    public static function reservasiFor(object|array $book): \Illuminate\Support\Collection
    {
        return static::reservationsFor($book);
    }

    public static function pengembalianFor(object|array $book): \Illuminate\Support\Collection
    {
        $bookId = is_array($book) ? ($book['id'] ?? null) : ($book->id ?? null);
        if ($bookId === null) {
            return collect();
        }

        return DB::table('pengembalian')->where('book_id', $bookId)->get();
    }

    public static function activeBorrowingsCount(object|array $book): int
    {
        $bookId = is_array($book) ? ($book['id'] ?? null) : ($book->id ?? null);
        if ($bookId === null) {
            return 0;
        }

        return (int) DB::table('pinjam')
            ->where('book_id', $bookId)
            ->where('status', 'dipinjam')
            ->count();
    }

    public static function availableStock(object|array $book): int
    {
        $stock = is_array($book) ? ($book['stock'] ?? 0) : ($book->stock ?? 0);
        $activeBorrowings = static::activeBorrowingsCount($book);

        return max(0, (int) $stock - $activeBorrowings);
    }

    public static function isAvailable(object|array $book): bool
    {
        $copyStatus = is_array($book) ? ($book['copy_status'] ?? null) : ($book->copy_status ?? null);
        $availableStock = static::availableStock($book);

        return $availableStock > 0 && !in_array($copyStatus, ['lost', 'damaged', 'maintenance'], true);
    }

    public static function isReservable(object|array $book): bool
    {
        $referenceOnly = is_array($book) ? ($book['reference_only'] ?? false) : ($book->reference_only ?? false);
        $copyStatus = is_array($book) ? ($book['copy_status'] ?? null) : ($book->copy_status ?? null);

        return ! (bool) $referenceOnly
            && !static::isAvailable($book)
            && !in_array($copyStatus, ['lost', 'damaged', 'maintenance'], true);
    }

    public static function canBeBorrowed(object|array $book): bool
    {
        $referenceOnly = is_array($book) ? ($book['reference_only'] ?? false) : ($book->reference_only ?? false);
        $copyStatus = is_array($book) ? ($book['copy_status'] ?? null) : ($book->copy_status ?? null);

        return ! (bool) $referenceOnly
            && static::availableStock($book) > 0
            && !in_array($copyStatus, ['lost', 'damaged', 'maintenance'], true);
    }

    public static function activeReservationFor(object|array $book): ?object
    {
        $bookId = is_array($book) ? ($book['id'] ?? null) : ($book->id ?? null);
        if ($bookId === null) {
            return null;
        }

        return DB::table('book_reservations')
            ->where('book_id', $bookId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', DB::raw('NOW()'))
            ->orderBy('queue_position')
            ->first();
    }

    public static function copyStatusLabel(object|array $book): string
    {
        $copyStatus = is_array($book) ? ($book['copy_status'] ?? null) : ($book->copy_status ?? null);

        return match ($copyStatus) {
            'available' => 'Tersedia',
            'borrowed' => 'Dipinjam',
            'reserved' => 'Direservasi',
            'lost' => 'Hilang',
            'damaged' => 'Rusak',
            'maintenance' => 'Perawatan',
            default => ucfirst((string) $copyStatus),
        };
    }

    public static function searchQuery(\Illuminate\Database\Query\Builder $query, string $keyword): \Illuminate\Database\Query\Builder
    {
        $kw = strtolower((string) $keyword);

        $query->where(function ($q) use ($kw) {
            $q->whereRaw('LOWER(judul) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(pengarang) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(kategori) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(subject) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(id_buku) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(COALESCE(isbn, \'\')) LIKE ?', ['%' . $kw . '%']);

            if (preg_match('/\d+/', (string) $kw) === 1) {
                $digits = preg_replace('/\D/', '', (string) $kw);
                $q->orWhereRaw('CAST(thn_terbit AS TEXT) LIKE ?', ['%' . $digits . '%']);
            }
        });

        return $query;
    }

    public static function filterByCategoryQuery(\Illuminate\Database\Query\Builder $query, ?string $category): \Illuminate\Database\Query\Builder
    {
        if (!$category) {
            return $query;
        }

        return $query->where(function ($q) use ($category) {
            $q->where('kategori', $category)
                ->orWhereExists(function ($sub) use ($category) {
                    $sub->select(DB::raw(1))
                        ->from('categories as c')
                        ->whereRaw('c.category_id = books.category_id')
                        ->where('c.name', $category);
                });
        });
    }
}

