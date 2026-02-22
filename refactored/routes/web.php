<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/',        fn () => redirect()->route('login'));

    Route::get('/login',   [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login',  [AuthenticatedSessionController::class, 'store'])
         ->middleware('throttle:6,1'); // OWASP A04: rate limit login

    Route::get('/register',  [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Authenticated user routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    // User orders
    Route::get('/my-orders',  [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders',    [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/details',      [OrderController::class, 'updateDetail'])->name('orders.updateDetail');
    Route::post('/orders/{order}/save-address', [OrderController::class, 'saveAddress'])->name('orders.saveAddress');
});

/*
|--------------------------------------------------------------------------
| Admin routes (auth + admin middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/orders',        [AdminOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/bulk',  [AdminOrderController::class, 'bulkApprove'])->name('orders.bulk');
});
