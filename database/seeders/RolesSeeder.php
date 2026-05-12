<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['librarian', 'member'] as $name) {
            Role::updateOrCreate(['name' => $name], ['name' => $name]);
        }
    }
}

