<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuickTransactionController;
use App\Http\Controllers\ReportController;
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

    Route::prefix('quick')->name('quick.')->group(function () {
        Route::get('/',  [QuickTransactionController::class, 'create'])->name('create');
        Route::post('/', [QuickTransactionController::class, 'store'])->name('store');
    });

    Route::resource('accounts', AccountController::class)->middleware('admin');
    Route::resource('categories', CategoryController::class)->middleware('admin');
    Route::resource('journals', JournalController::class)->middleware('admin');

    // Products: read-only untuk semua user; create/edit/delete khusus admin
    Route::get('products',                 [ProductController::class, 'index'])->name('products.index');
    Route::middleware('admin')->group(function () {
        Route::get('products/create',          [ProductController::class, 'create'])->name('products.create');
        Route::post('products',                [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit',  [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}',       [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}',    [ProductController::class, 'destroy'])->name('products.destroy');
    });
    Route::get('products/{product}',       [ProductController::class, 'show'])->name('products.show');

    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/',                       [StockMovementController::class, 'index'])->name('index');
        Route::get('/create',                 [StockMovementController::class, 'create'])->name('create');
        Route::post('/',                      [StockMovementController::class, 'store'])->name('store');
        Route::get('/report',                 [StockMovementController::class, 'report'])->name('report');
        Route::get('/card/{product}',         [StockMovementController::class, 'card'])->name('card');
        Route::get('/{stock}',                [StockMovementController::class, 'show'])->name('show');
        Route::delete('/{stock}',             [StockMovementController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware('admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/ledger',           [ReportController::class, 'ledger'])->name('ledger');
        Route::get('/trial-balance',    [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('/balance-sheet',    [ReportController::class, 'balanceSheet'])->name('balance-sheet');
    });
});
