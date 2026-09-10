<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

class CacheController extends Controller
{
    /**
     * Clear all application, route, view, configuration, and permission caches.
     */
    public function clear(Request $request): RedirectResponse
    {
        // 1. Clear core Laravel caches
        Artisan::call('optimize:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        // 2. Reset Spatie permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 3. Clear AdminMenu cache
        AdminMenu::flushCache();

        return back()->with('success', 'System cache, views, routes, config, and permissions cleared successfully!');
    }
}
