<?php

namespace App\Policies;

use App\Models\Pengembalian;
use App\Models\User;

class ReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isLibrarian();
    }

    public function view(User $user, Pengembalian $pengembalian): bool
    {
        if ($user->isLibrarian()) {
            return true;
        }

        return $pengembalian->anggota?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isLibrarian();
    }

    public function delete(User $user, Pengembalian $pengembalian): bool
    {
        return $user->isLibrarian();
    }
}
