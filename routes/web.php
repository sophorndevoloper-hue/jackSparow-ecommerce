<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Web Routes
|--------------------------------------------------------------------------
|
| Modular route structure:
| - routes/frontend/web.php   : Customer storefront, shopping cart & customer auth routes
| - routes/backend/web.php    : Backend Admin dashboard & management routes
|
*/

// Customer Storefront & Auth Routes
require __DIR__.'/frontend/web.php';

// Backend Admin Dashboard & Management Routes
require __DIR__.'/backend/web.php';

// Unified Dashboard Redirect
Route::get('/dashboard', function () {
    if (auth('backend')->check() || auth()->user()?->hasRole('admin', 'backend')) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('home');
})->middleware(['auth:backend,frontend'])->name('dashboard');

// Unified User Account Profile
Route::middleware('auth:backend,frontend')->group(function () {
    Route::get('/profile', function () {
        if (auth('backend')->check()) {
            return redirect()->route('admin.profile.edit');
        }

        return app(ProfileController::class)->edit(request());
    })->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
