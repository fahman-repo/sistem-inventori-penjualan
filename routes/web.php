<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierDebtController;
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
    Route::resource('admin/suppliers', SupplierController::class);
    Route::get('admin/supplier-debts', [SupplierDebtController::class, 'index'])->name('supplier-debts.index');
    Route::get('admin/supplier-debts/export', [SupplierDebtController::class, 'export'])->name('supplier-debts.export');
    Route::get('admin/supplier-debts/{supplierDebt}', [SupplierDebtController::class, 'show'])->name('supplier-debts.show');
    Route::post('admin/supplier-debts/{supplierDebt}/payments', [SupplierDebtController::class, 'storePayment'])->name('supplier-debts.payments.store');
    Route::get('admin/reports/stock', [App\Http\Controllers\ReportController::class, 'stock'])->name('reports.stock');
    Route::get('admin/reports/sales', [App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('admin/reports/sales/export', [App\Http\Controllers\ReportController::class, 'exportSalesExcel'])->name('reports.sales.export');
    Route::get('admin/reports/profit', [App\Http\Controllers\ReportController::class, 'profit'])->name('reports.profit');
    Route::get('admin/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('admin/stock-opnames', [App\Http\Controllers\StockOpnameController::class, 'index'])->name('stock-opnames.index');
    Route::get('admin/stock-opnames/create', [App\Http\Controllers\StockOpnameController::class, 'create'])->name('stock-opnames.create');
    Route::post('admin/stock-opnames', [App\Http\Controllers\StockOpnameController::class, 'store'])->name('stock-opnames.store');
    Route::get('admin/stock-opnames/{stockOpname}', [App\Http\Controllers\StockOpnameController::class, 'show'])->name('stock-opnames.show');
    Route::resource('admin/users', App\Http\Controllers\UserController::class);
});

// Sales routes - for all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::resource('sales', SaleController::class);
    Route::get('sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
    Route::get('notifications/low-stock', App\Http\Controllers\NotificationController::class)
        ->name('notifications.low-stock');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';