<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddStorageSettingsMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settingsParent = \App\Models\Menu::where('name', 'Settings')->first();
        
        if ($settingsParent) {
            $menu = \App\Models\Menu::create([
                'name' => 'Storage Settings',
                'route' => 'settings.storage',
                'icon' => 'database',
                'parent_id' => $settingsParent->id,
                'order' => 2,
                'is_active' => true,
            ]);

            $rootRole = \App\Models\Role::where('name', 'root')->first();
            if ($rootRole) {
                $menu->roles()->sync([$rootRole->id]);
            }
        }
    }
}
