<?php

namespace App\Policies;

use App\Models\MembershipRequest;
use App\Models\User;

class MembershipPolicy
{
    public function create(User $user): bool
    {
        return $user->isMember();
    }

    public function viewAny(User $user): bool
    {
        return $user->isLibrarian();
    }

    public function view(User $user, MembershipRequest $membershipRequest): bool
    {
        return $user->isLibrarian() || $membershipRequest->user_id === $user->id;
    }

    public function update(User $user, MembershipRequest $membershipRequest): bool
    {
        return $user->isLibrarian() && $membershipRequest->status === 'pending';
    }
}
