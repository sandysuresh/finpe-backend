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
        return array_keys(array_filter(
            self::all(),
            fn (array $module) => empty($module['inherits'])
        ));
    }

    public static function label(string $module): string
    {
        return self::all()[$module]['label'] ?? $module;
    }

    public static function navItems(Admin $admin): array
    {
        $items = [];

        foreach (self::all() as $key => $module) {
            if (! $admin->hasModule($module['inherits'] ?? $key)) {
                continue;
            }

            $route = $module['route'] ?? null;
            $url = $route && \Illuminate\Support\Facades\Route::has($route)
                ? route($route)
                : '#';

            $children = [];
            foreach ($module['children'] ?? [] as $child) {
                $childRoute = $child['route'] ?? null;
                if (! $childRoute || ! \Illuminate\Support\Facades\Route::has($childRoute)) {
                    continue;
                }

                $children[] = [
                    'label' => $child['label'],
                    'url' => route($childRoute),
                    'active' => request()->routeIs($childRoute) || request()->routeIs($childRoute.'*'),
                ];
            }

            $active = $route ? request()->routeIs($route) || request()->routeIs($route.'*') : false;
            if (! $active) {
                foreach ($children as $child) {
                    if ($child['active']) {
                        $active = true;
                        break;
                    }
                }
            }

            $items[] = [
                'label' => $module['label'],
                'url' => $url,
                'active' => $active,
                'children' => $children,
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
