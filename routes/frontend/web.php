<?php

use App\Http\Controllers\Frontend\Auth\CustomerLoginController;
use App\Http\Controllers\Frontend\Auth\CustomerRegisterController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
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

// Homepage & Catalog
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

// Shopping Cart
Route::prefix('cart')->as('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
    Route::post('/update/{product}', [CartController::class, 'update'])->name('update');
    Route::post('/remove/{product}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
});

// Checkout & Order Tracking
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/order/success/{order_number}', [OrderController::class, 'show'])->name('order.success');
Route::get('/my-orders', [OrderController::class, 'myOrders'])->middleware('auth:frontend,backend')->name('my-orders');
