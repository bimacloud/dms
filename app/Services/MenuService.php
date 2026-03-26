<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Support\Collection;

class MenuService
{
    /**
     * Get the menu tree for a specific role.
     */
    public function getMenuTreeByRole(Role $role, $position = null): Collection
    {
        // Get all active menus assigned to this role
        $query = $role->menus()->where('is_active', true);

        if ($position) {
            $query->where('position', $position);
        }

        $menus = $query->orderBy('order')->get();

        return $this->buildTree($menus);
    }

    /**
     * Build nested hierarchy from flat collection.
     */
    protected function buildTree(Collection $menus, $parentId = null): Collection
    {
        $branch = collect();

        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $children = $this->buildTree($menus, $menu->id);
                if ($children->isNotEmpty()) {
                    $menu->setRelation('children', $children);
                }
                $branch->push($menu);
            }
        }

        return $branch;
    }
}
