<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolesSeeder::class);

        $user = User::updateOrCreate(
            ['email' => 'admin@perpusku.id'],
            [
                'name' => 'Administrator',
                'password' => 'Admin1234!',
            ]
        );

        $librarianRole = Role::where('name', 'librarian')->first();
        if ($librarianRole) {
            $user->roles()->syncWithoutDetaching([$librarianRole->id]);
        }
    }
}
