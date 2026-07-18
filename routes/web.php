<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes - only for admin role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('admin/categories', CategoryController::class);
    Route::resource('admin/products', ProductController::class);
    Route::resource('admin/purchases', PurchaseController::class);
    Route::get('admin/reports/stock', [App\Http\Controllers\ReportController::class, 'stock'])->name('reports.stock');
    Route::get('admin/reports/sales', [App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('admin/reports/profit', [App\Http\Controllers\ReportController::class, 'profit'])->name('reports.profit');
});

// Sales routes - for all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::resource('sales', SaleController::class);
    Route::get('sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';