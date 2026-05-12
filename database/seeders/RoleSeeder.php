<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $librarianRole = Role::firstOrCreate(
            ['name' => 'librarian'],
            ['display_name' => 'Librarian', 'description' => 'Can manage all library operations']
        );

        $memberRole = Role::firstOrCreate(
            ['name' => 'member'],
            ['display_name' => 'Member', 'description' => 'Can borrow books and view own data']
        );

        // Create default admin user if not exists
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@perpusku.id'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('admin1234'),
            ]
        );

        // Pivot table 'role_user' only contains (id, role_user_id, user_id) or (id, role_id, user_id)
        // In this project, migration/table definition is corrupted, so use direct insert to avoid relying on pivot columns.
        if (!$adminUser->hasRole($librarianRole->id)) {
            $adminUser->roles()->attach($librarianRole->id);
        }
    }
}
