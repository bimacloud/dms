<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $menus = collect();
            $headerMenus = collect();
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                if ($user->role) {
                    $menuService = new \App\Services\MenuService();
                    $menus = $menuService->getMenuTreeByRole($user->role, 'sidebar');
                    $headerMenus = $menuService->getMenuTreeByRole($user->role, 'top_right');
                }
            }
            $view->with([
                'menus' => $menus,
                'headerMenus' => $headerMenus
            ]);
        });
    }
}
