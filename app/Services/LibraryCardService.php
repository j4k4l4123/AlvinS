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
        $sequence ??= LibraryCard::count() + 1;

        return 'MEM-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
