<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Pinjam;

class InventoryService
{
    public function generateCopyCode(Book $book): string
    {
        $prefix = $book->copy_code_prefix ?: $book->id_buku;
        $latestId = (int) Pinjam::max('id') + 1;

        return strtoupper($prefix) . '-COPY-' . str_pad((string) $latestId, 5, '0', STR_PAD_LEFT);
    }

    public function refreshBookStatus(Book $book): void
    {
        if ($book->copy_status === 'lost' || $book->copy_condition === 'lost') {
            $book->update(['copy_status' => 'lost']);
            return;
        }

        if ($book->copy_status === 'maintenance') {
            return;
        }

        if ($book->copy_condition === 'damaged') {
            $book->update(['copy_status' => 'damaged']);
            return;
        }

        $hasReservation = BookReservation::where('book_id', $book->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->exists();

        $activeBorrows = Pinjam::where('book_id', $book->id)
            ->where('status', 'dipinjam')
            ->count();

        if ($activeBorrows >= (int) $book->stock) {
            $book->update(['copy_status' => 'borrowed']);
            return;
        }

        if ($hasReservation) {
            $book->update(['copy_status' => 'reserved']);
            return;
        }

        $book->update(['copy_status' => 'available']);
    }

    public function markAsLost(Book $book): void
    {
        $book->update(['copy_status' => 'lost', 'copy_condition' => 'lost']);
    }

    public function markAsDamaged(Book $book): void
    {
        $book->update(['copy_status' => 'damaged', 'copy_condition' => 'damaged']);
    }

    public function markAsAvailable(Book $book): void
    {
        if ($book->copy_condition !== 'lost') {
            $book->update(['copy_condition' => 'good']);
        }
        $this->refreshBookStatus($book->fresh());
    }
}
