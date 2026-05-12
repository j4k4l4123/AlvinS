<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pinjam;
use Illuminate\Auth\Access\Response;

class ReturnPolicy
{
 /**
 * Determine whether the user can view any pengembalian.
 */
 public function viewAny(User \): bool
 {
 return \->hasRole('librarian');
 }

 /**
 * Determine whether the user can view their own pengembalian.
 */
 public function view(User \, Pinjam \): bool
 {
 if (\->hasRole('librarian')) {
 return true;
 }

 return \->memberProfile &&
 \->anggota_id == \->memberProfile->id_anggota;
 }

 /**
 * Determine whether the user can create pengembalian.
 */
 public function create(User \): bool
 {
 return \->hasRole('librarian');
 }
}
