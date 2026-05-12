<?php

namespace App\Policies;

use App\Models\User;

class BookPolicy
{
 /**
 * Determine whether the user can view the book catalog.
 */
 public function viewAny(User \): bool
 {
 return true; // Both librarians and members can view
 }

 /**
 * Determine whether the user can manage books.
 */
 public function manage(User \): bool
 {
 return \->hasRole('librarian');
 }
}
