<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Pinjam;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function generateCopyCode(object $book): string
    {
        $prefix = $book->copy_code_prefix ?: $book->id_buku;
        $latestId = (int) DB::table('pinjam')->max('id') + 1;

        return strtoupper($prefix) . '-COPY-' . str_pad((string) $latestId, 5, '0', STR_PAD_LEFT);
    }

    public function refreshBookStatus(object $book): void
    {
        if ($book->copy_status === 'lost' || $book->copy_condition === 'lost') {
            DB::table('books')->where('id', $book->id)->update(['copy_status' => 'lost']);
            return;
        }

        if ($book->copy_status === 'maintenance') {
            return;
        }

        if ($book->copy_condition === 'damaged') {
            DB::table('books')->where('id', $book->id)->update(['copy_status' => 'damaged']);
            return;
        }

        $hasReservation = DB::table('book_reservations')->where('book_id', $book->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->exists();

        $activeBorrows = DB::table('pinjam')->where('book_id', $book->id)
            ->where('status', 'dipinjam')
            ->count();

        if ($activeBorrows >= (int) $book->stock) {
            DB::table('books')->where('id', $book->id)->update(['copy_status' => 'borrowed']);
            return;
        }

        if ($hasReservation) {
            DB::table('books')->where('id', $book->id)->update(['copy_status' => 'reserved']);
            return;
        }

        DB::table('books')->where('id', $book->id)->update(['copy_status' => 'available']);
    }

    public function markAsLost(object $book): void
    {
        DB::table('books')->where('id', $book->id)->update(['copy_status' => 'lost', 'copy_condition' => 'lost']);
    }

    public function markAsDamaged(object $book): void
    {
        DB::table('books')->where('id', $book->id)->update(['copy_status' => 'damaged', 'copy_condition' => 'damaged']);
    }

    public function markAsAvailable(object $book): void
    {
        if ($book->copy_condition !== 'lost') {
            DB::table('books')->where('id', $book->id)->update(['copy_condition' => 'good']);
        }
        $this->refreshBookStatus(DB::table('books')->where('id', $book->id)->first());
    }
}
