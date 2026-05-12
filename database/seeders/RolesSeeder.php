<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'librarian'],
            [
                'display_name' => 'Librarian',
                'description' => 'Can manage all library operations',
            ]
        );

        Role::updateOrCreate(
            ['name' => 'member'],
            [
                'display_name' => 'Member',
                'description' => 'Can borrow books and view own data',
            ]
        );
    }
}
