<?php

namespace App\Policies;

use App\Models\User;

class MembershipPolicy
{
 /**
 * Determine whether the user can cancel membership.
 */
 public function cancel(User \): bool
 {
 return \->hasRole('member');
 }

 /**
 * Determine whether the user can manage memberships.
 */
 public function manage(User \): bool
 {
 return \->hasRole('librarian');
 }
}
