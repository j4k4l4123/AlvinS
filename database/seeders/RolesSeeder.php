<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->updateOrInsert(
            ['name' => 'librarian'],
            [
                'display_name' => 'Librarian',
                'description' => 'Can manage all library operations',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'member'],
            [
                'display_name' => 'Member',
                'description' => 'Can borrow books and view own data',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
