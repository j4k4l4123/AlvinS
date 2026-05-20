<?php

namespace App\Services;

use App\Models\LibraryCard;

class LibraryCardService
{
    public function generateCardNumber(): string
    {
        return 'LIB-' . strtoupper(substr(bin2hex(random_bytes(8)), 0, 10));
    }

    public function generateSequentialCardNumber(?int $sequence = null): string
    {
        if ($sequence === null) {
            $lastCardNumber = LibraryCard::query()
                ->where('card_number', 'like', 'MEM-%')
                ->orderByDesc('id')
                ->value('card_number');

            $lastSequence = 0;

            if ($lastCardNumber && preg_match('/^MEM-(\d+)$/', $lastCardNumber, $matches)) {
                $lastSequence = (int) $matches[1];
            }

            $sequence = $lastSequence + 1;
        }

        return 'MEM-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
