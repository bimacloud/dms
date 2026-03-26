<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = \App\Models\Role::all();

        foreach ($roles as $role) {
            \App\Models\User::firstOrCreate(
                ['email' => strtolower($role->name) . '@example.com'],
                [
                    'name' => $role->name . ' User',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role_id' => $role->id,
                ]
            );
        }
    }
}
