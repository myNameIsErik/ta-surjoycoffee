<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConsigneeController;
use App\Http\Controllers\ConsignmentController;
use App\Http\Controllers\ConsignmentProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master data
    Route::resource('categories', CategoryController::class)->middleware('admin');

    // Products: read-only untuk semua user; write khusus admin
    Route::get('products',                 [ProductController::class, 'index'])->name('products.index');
    Route::middleware('admin')->group(function () {
        Route::get('products/create',          [ProductController::class, 'create'])->name('products.create');
        Route::post('products',                [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit',  [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}',       [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}',    [ProductController::class, 'destroy'])->name('products.destroy');
    });
    Route::get('products/{product}',       [ProductController::class, 'show'])->name('products.show');

    // Stok
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/',                       [StockMovementController::class, 'index'])->name('index');
        Route::get('/create',                 [StockMovementController::class, 'create'])->name('create');
        Route::post('/',                      [StockMovementController::class, 'store'])->name('store');
        Route::get('/report',                 [StockMovementController::class, 'report'])->name('report');
        Route::get('/sales-report',           [StockMovementController::class, 'salesReport'])->name('sales-report');
        Route::get('/purchase-report',        [StockMovementController::class, 'purchaseReport'])->name('purchase-report');
        Route::get('/card/{product}',         [StockMovementController::class, 'card'])->name('card');
        Route::get('/{stock}',                [StockMovementController::class, 'show'])->name('show');
        Route::delete('/{stock}',             [StockMovementController::class, 'destroy'])->name('destroy');
    });

    // Konsinyasi
    Route::resource('consignees', ConsigneeController::class)->middleware('admin');

    // Master barang konsinyasi: read-only untuk semua; write khusus admin
    Route::get('consignment-products', [ConsignmentProductController::class, 'index'])->name('consignment-products.index');
    Route::middleware('admin')->group(function () {
        Route::get('consignment-products/create',                     [ConsignmentProductController::class, 'create'])->name('consignment-products.create');
        Route::post('consignment-products',                           [ConsignmentProductController::class, 'store'])->name('consignment-products.store');
        Route::get('consignment-products/{consignment_product}/edit', [ConsignmentProductController::class, 'edit'])->name('consignment-products.edit');
        Route::put('consignment-products/{consignment_product}',      [ConsignmentProductController::class, 'update'])->name('consignment-products.update');
        Route::delete('consignment-products/{consignment_product}',   [ConsignmentProductController::class, 'destroy'])->name('consignment-products.destroy');
    });

    Route::prefix('consignments')->name('consignments.')->group(function () {
        Route::get('/',                          [ConsignmentController::class, 'index'])->name('index');
        Route::get('/create',                    [ConsignmentController::class, 'create'])->name('create');
        Route::post('/',                         [ConsignmentController::class, 'store'])->name('store');
        Route::get('/position',                  [ConsignmentController::class, 'position'])->name('position');
        Route::get('/outstanding',               [ConsignmentController::class, 'outstanding'])->name('outstanding');
        Route::get('/{consignment}',             [ConsignmentController::class, 'show'])->name('show');
        Route::delete('/{consignment}',          [ConsignmentController::class, 'destroy'])->name('destroy');
    });

    // User management (admin only)
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class);
    });
});
