<?php

namespace App\Support;

use App\Models\Admin;

class AdminModules
{
    public static function all(): array
    {
        return config('admin_modules', []);
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function label(string $module): string
    {
        return self::all()[$module]['label'] ?? $module;
    }

    public static function navItems(Admin $admin): array
    {
        $items = [];

        foreach (self::all() as $key => $module) {
            if (! $admin->hasModule($key)) {
                continue;
            }

            $route = $module['route'] ?? null;
            $url = $route && \Illuminate\Support\Facades\Route::has($route)
                ? route($route)
                : '#';

            $items[] = [
                'label' => $module['label'],
                'url' => $url,
                'active' => $route ? request()->routeIs($route) || request()->routeIs($route.'*') : false,
            ];
        }

        return $items;
    }

    public static function firstUrl(Admin $admin): string
    {
        foreach (self::all() as $key => $module) {
            if (! $admin->hasModule($key)) {
                continue;
            }

            $route = $module['route'] ?? null;
            if ($route && \Illuminate\Support\Facades\Route::has($route)) {
                return route($route);
            }
        }

        return route('admin.login');
    }
}
