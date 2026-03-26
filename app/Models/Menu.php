<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'route', 'icon', 'parent_id', 'order', 'is_active', 'position'];

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menu');
    }
}
