<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dashboard
        $dashboard = $this->createMenu('Dashboard', 'dashboard', 'layout-grid', null, 1, ['root', 'Manager', 'NOC', 'CS', 'Teknisi']);

        // 2. Documents (Parent)
        $docsParent = $this->createMenu('Documents', null, 'folder', null, 2, ['root', 'Manager', 'NOC', 'CS', 'Teknisi']);
        
        // Children of Documents
        $this->createMenu('All Documents', 'documents.index', 'file-text', $docsParent->id, 1, ['root', 'Manager', 'NOC', 'CS', 'Teknisi']);
        
        // 3. Categories
        $this->createMenu('Categories', 'categories.index', 'layers', null, 3, ['root', 'Manager']);

        // 4. User Management (root only)
        $this->createMenu('User Management', 'users.index', 'users', null, 4, ['root']);

        // 5. Settings (Parent - root only)
        $settingsParent = $this->createMenu('Settings', null, 'settings', null, 5, ['root']);
        
        // Children of Settings
        $this->createMenu('Menu Management', 'menus.index', 'menu', $settingsParent->id, 1, ['root']);

        // 6. Role Specific Mock Menus
        $this->createMenu('Network Status', 'network.index', 'activity', null, 6, ['root', 'NOC']);
        $this->createMenu('Customers', 'customers.index', 'users-2', null, 7, ['root', 'CS']);
        $this->createMenu('Tickets', 'tickets.index', 'ticket', null, 8, ['root', 'Teknisi']);
    }

    protected function createMenu($name, $route, $icon, $parentId, $order, $roles)
    {
        $menu = Menu::create([
            'name' => $name,
            'route' => $route,
            'icon' => $icon,
            'parent_id' => $parentId,
            'order' => $order,
            'is_active' => true,
        ]);

        $roles = Role::whereIn('name', $roles)->get();
        $menu->roles()->sync($roles);

        return $menu;
    }
}
