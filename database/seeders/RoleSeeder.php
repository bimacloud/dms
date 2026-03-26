<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Menu;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'root',
            'Manager',
            'NOC',
            'CS',
            'Teknisi'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
