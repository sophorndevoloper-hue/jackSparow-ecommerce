<?php

use App\Http\Controllers\Backend\Auth\BackendAuthController;
use App\Http\Controllers\Backend\BackendMenuController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\CacheController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\CustomerGroupController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\MakeController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProductImageController;
use App\Http\Controllers\Backend\ProfileController as BackendProfileController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SerialNumberController;
use App\Http\Controllers\Backend\StockAdjustmentController;
use App\Http\Controllers\Backend\StockController;
use App\Http\Controllers\Backend\StockTransferController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend Admin Portal & Dashboard Routes
|--------------------------------------------------------------------------
|
| Protected routes for store administrators to manage hardware inventory,
| categories, manufacturers, orders, customer accounts, and settings.
| All actions are protected and role-controlled via CheckBackendPermission.
|
*/

// Guest Admin Authentication
Route::prefix('admin')->as('admin.')->group(function () {
    Route::middleware('guest:backend')->group(function () {
        Route::get('login', [BackendAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [BackendAuthController::class, 'login']);
        Route::get('register', [BackendAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [BackendAuthController::class, 'register']);
    });

    Route::post('logout', [BackendAuthController::class, 'logout'])->middleware('auth:backend')->name('logout');
});

// Protected Admin Portal (Role & Permission controlled via CheckBackendPermission)
Route::prefix('admin')->middleware(['auth:backend', 'admin.permission'])->as('admin.')->group(function () {
    // 1. Dashboard & Profile
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profile', [BackendProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [BackendProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [BackendProfileController::class, 'updatePassword'])->name('profile.password');

    // 2. Product Images & Media Gallery
    Route::get('products/images', [ProductImageController::class, 'index'])->name('products.images.index');
    Route::post('products/images/save', [ProductImageController::class, 'saveGallery'])->name('products.images.save');
    Route::post('products/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::patch('products/images/{image}/primary', [ProductImageController::class, 'setPrimary'])->name('products.images.primary');
    Route::delete('products/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');

    // 3. Stock Management & Serials
    Route::resource('stock/adjustments', StockAdjustmentController::class)->names('stock.adjustments');
    Route::resource('stock/transfers', StockTransferController::class)->names('stock.transfers');
    Route::patch('stock/transfers/{transfer}/status', [StockTransferController::class, 'updateStatus'])->name('stock.transfers.status');
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('stock/{product}', [StockController::class, 'update'])->name('stock.update');
    Route::post('products/{product}/serials', [ProductController::class, 'storeSerials'])->name('products.serials.store');
    Route::post('serial-numbers/import', [SerialNumberController::class, 'import'])->name('serial-numbers.import');
    Route::get('serial-numbers/templates/{format}', [SerialNumberController::class, 'downloadTemplate'])->name('serial-numbers.templates.download');
    Route::resource('serial-numbers', SerialNumberController::class)->only(['index', 'store', 'update', 'destroy']);

    // 4. Warehouses
    Route::resource('warehouses', WarehouseController::class);

    // 5. Hardware Catalog (Products, Categories, Brands, Makes)
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('makes', MakeController::class);

    // 6. Sales & Order Fulfillment
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    // 7. B2B Accounts & People (Customer Groups, Customers, Suppliers)
    Route::resource('customer-groups', CustomerGroupController::class);
    Route::resource('customers', CustomerController::class)->only(['index', 'show']);
    Route::patch('customers/{customer}/toggle-special', [CustomerController::class, 'toggleSpecial'])->name('customers.toggle-special');
    Route::resource('suppliers', SupplierController::class);

    // 8. System Settings & Access Control
    Route::post('clear-cache', [CacheController::class, 'clear'])->name('cache.clear');
    Route::patch('users/{user}/toggle-approval', [UserController::class, 'toggleApproval'])->name('users.toggle-approval');
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::resource('roles', RoleController::class);

    // 9. Menu Setup
    Route::get('menus', [BackendMenuController::class, 'index'])->name('menus.index');
    Route::post('menus/sync', [BackendMenuController::class, 'sync'])->name('menus.sync');
    Route::get('menus/{menu}/edit', [BackendMenuController::class, 'edit'])->name('menus.edit');
    Route::put('menus/{menu}', [BackendMenuController::class, 'update'])->name('menus.update');
    Route::patch('menus/{menu}/sort', [BackendMenuController::class, 'updateSort'])->name('menus.sort');
    Route::patch('menus/{menu}/toggle-active', [BackendMenuController::class, 'toggleActive'])->name('menus.toggle-active');
});
