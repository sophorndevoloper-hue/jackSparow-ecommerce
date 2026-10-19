<?php

use App\Http\Controllers\Frontend\Auth\CustomerLoginController;
use App\Http\Controllers\Frontend\Auth\CustomerRegisterController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\CustomerProfileController;
use App\Http\Controllers\Frontend\FavoriteController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\RecentlyViewedController;
use App\Http\Controllers\Frontend\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront Customer Routes
|--------------------------------------------------------------------------
|
| Public browsing, catalog filters, shopping cart, checkout, customer orders,
| and customer authentication.
|
*/

// Customer Authentication
Route::middleware('guest:frontend')->group(function () {
    Route::get('register', [CustomerRegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [CustomerRegisterController::class, 'register']);
    Route::get('login', [CustomerLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [CustomerLoginController::class, 'login']);
});
Route::post('logout', [CustomerLoginController::class, 'logout'])->name('logout');

// Homepage, Catalog & Public Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/about-us', [PageController::class, 'about'])->name('about');
Route::get('/contact-us', [PageController::class, 'contact'])->name('contact');

// Shopping Cart
Route::prefix('cart')->as('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
    Route::post('/update/{product}', [CartController::class, 'update'])->name('update');
    Route::post('/remove/{product}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
});

// Product Favorites & Wishlist
Route::prefix('favorites')->as('favorites.')->group(function () {
    Route::get('/', [FavoriteController::class, 'index'])->name('index');
    Route::post('/toggle/{product}', [FavoriteController::class, 'toggle'])->name('toggle');
    Route::post('/clear', [FavoriteController::class, 'clear'])->name('clear');
});

// Recently Viewed Products
Route::prefix('recently-viewed')->as('recently-viewed.')->group(function () {
    Route::get('/', [RecentlyViewedController::class, 'index'])->name('index');
    Route::post('/clear', [RecentlyViewedController::class, 'clear'])->name('clear');
});

// Customer Profile, Checkout & Order Tracking (Exclusively for frontend_user customers)
Route::middleware('auth:frontend')->group(function () {
    Route::get('/customer/profile', [CustomerProfileController::class, 'edit'])->name('customer.profile');
    Route::put('/customer/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
    Route::put('/customer/profile/password', [CustomerProfileController::class, 'updatePassword'])->name('customer.profile.password');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('my-orders');
});
Route::get('/order/success/{order_number}', [OrderController::class, 'show'])->name('order.success');
