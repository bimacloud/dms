<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use App\Http\Requests\MenuRequest;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with(['roles', 'parent'])->orderBy('order')->get();
        $roles = Role::all();
        $parentMenus = Menu::whereNull('parent_id')->get();
        
        $menusData = $menus->map(function($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'route' => $m->route,
                'icon' => $m->icon,
                'parent_id' => $m->parent_id,
                'parent_name' => $m->parent ? $m->parent->name : null,
                'position' => $m->position,
                'order' => $m->order,
                'is_active' => (bool)$m->is_active,
                'roles' => $m->roles->pluck('id')->toArray(),
                'role_names' => $m->roles->pluck('name')->toArray()
            ];
        });

        return view('menus.index', compact('menus', 'roles', 'parentMenus', 'menusData'));
    }

    public function store(MenuRequest $request)
    {
        $menu = Menu::create($request->validated());
        
        if ($request->has('roles')) {
            $menu->roles()->sync($request->roles);
        }

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        $menu->update($request->validated());
        
        $menu->roles()->sync($request->roles ?? []);

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu)
    {
        if (auth()->user()->role->name !== 'root') {
            abort(403, 'Hanya role Root yang diperbolehkan menghapus menu.');
        }

        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully.');
    }
}
