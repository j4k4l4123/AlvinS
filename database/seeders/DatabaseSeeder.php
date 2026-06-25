<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        $librarianRole = Role::findByName('librarian');
        if ($librarianRole) {
            DB::table('role_user')->updateOrInsert([
                'user_id' => $user->id,
                'role_id' => $librarianRole->id,
            ]);
        }
    }
}
