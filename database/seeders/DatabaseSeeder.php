<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure roles exist
        $this->call(RolesSeeder::class);

        // Seed a minimal user
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Attach librarian role so you can login and be redirected correctly
        $librarianRole = Role::firstOrCreate(['name' => 'librarian'], ['name' => 'librarian']);
        $user->roles()->syncWithoutDetaching([$librarianRole->id]);

        // Optionally also attach member role (harmless)
        $memberRole = Role::firstOrCreate(['name' => 'member'], ['name' => 'member']);
        $user->roles()->syncWithoutDetaching([$memberRole->id]);
    }
}

