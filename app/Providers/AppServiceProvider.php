<?php

namespace App\Providers;

use App\Models\AdminMenu;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Delivery;
use App\Models\DeliveryAgent;
use App\Models\FrontendUser;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\ProductImage;
use App\Models\ProductModel;
use App\Models\Quote;
use App\Models\SerialNumber;
use App\Models\ShippingMethod;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Warehouse;
use App\Observers\AuditObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        // 1. Force HTTPS and correct asset paths when deployed on Render / production
        if ($this->app->environment('production') || env('RENDER') || str_starts_with(config('app.url', ''), 'https://')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }

        if (config('app.asset_url')) {
            URL::forceRootUrl(config('app.asset_url'));
        }

        // 2. Pagination
        Paginator::useBootstrapFive();

        // 3. Grant all permissions implicitly to users with the 'admin' or 'superadmin' role
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole')) {
                if ($user->hasRole('superadmin', 'backend') || $user->hasRole('admin', 'backend') || $user->hasRole('superadmin') || $user->hasRole('admin')) {
                    return true;
                }
            }

            return null;
        });

        // 4. Audit Authentication Events (Login / Logout)
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            $guard = $event->guard ?? 'web';
            $name = $user->name ?? $user->email ?? 'User';
            $role = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : null;

            $description = "User '{$name}' logged in ({$guard} guard".($role ? " - {$role}" : '').').';

            UserActivity::log(
                action: 'LOGIN',
                module: 'Auth',
                description: $description,
                entity: $user,
                properties: [
                    'guard' => $guard,
                    'email' => $user->email ?? null,
                    'role' => $role,
                ],
                user: $user
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;
            if ($user) {
                $guard = $event->guard ?? 'web';
                $name = $user->name ?? $user->email ?? 'User';

                $description = "User '{$name}' logged out ({$guard} guard).";

                UserActivity::log(
                    action: 'LOGOUT',
                    module: 'Auth',
                    description: $description,
                    entity: $user,
                    properties: [
                        'guard' => $guard,
                        'email' => $user->email ?? null,
                    ],
                    user: $user
                );
            }
        });

        // 5. Automated Eloquent Audit Observer for CREATE, UPDATE, DELETE
        $auditedModels = [
            Product::class,
            ProductCollection::class,
            ProductImage::class,
            Category::class,
            Brand::class,
            ProductModel::class,
            Order::class,
            Quote::class,
            Warehouse::class,
            StockAdjustment::class,
            StockTransfer::class,
            Customer::class,
            CustomerGroup::class,
            Supplier::class,
            DeliveryAgent::class,
            Delivery::class,
            Staff::class,
            StaffAttendance::class,
            StaffPayroll::class,
            User::class,
            FrontendUser::class,
            AdminMenu::class,
            ShippingMethod::class,
            PaymentMethod::class,
            SerialNumber::class,
            Role::class,
            Permission::class,
        ];

        foreach ($auditedModels as $modelClass) {
            if (class_exists($modelClass)) {
                $modelClass::observe(AuditObserver::class);
            }
        }

        // 6. Blade Admin Components & Aliases
        Blade::anonymousComponentPath(resource_path('views/admin/component'), 'admin');
        Blade::component('admin.component.datatable', 'admin-datatable');
        Blade::component('admin.component.model', 'admin-modal');
        Blade::component('admin.component.toast', 'admin-toast');
        Blade::component('admin.component.confirm-modal', 'admin-confirm-modal');
    }
}