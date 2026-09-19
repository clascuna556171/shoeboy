<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Central Role-Aware Workspace / Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory & Triage Module
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::patch('/items/{item}/triage', [ItemController::class, 'updateTriage'])->name('items.triage');

    // Batches Module
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');

    // Orders & Walk-in POS Module
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/award', [OrderController::class, 'award'])->name('orders.award');
    Route::post('/orders/pos-checkout', [OrderController::class, 'posCheckout'])->name('orders.pos-checkout');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Payment Verification Module
    Route::post('/payments/verify', [PaymentController::class, 'verify'])->name('payments.verify');

    // Deliveries & Fulfillment Module
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::put('/deliveries/{delivery}', [DeliveryController::class, 'update'])->name('deliveries.update');

    // Operating Expenses Module
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Owner-Only Protected Routes
    Route::middleware('role:owner')->group(function () {
        // Financial & Profit Reports Module
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

        // Staff Account Management Module
        Route::get('/staff', [UserController::class, 'index'])->name('staff.index');
        Route::post('/staff', [UserController::class, 'store'])->name('staff.store');
        Route::put('/staff/{user}', [UserController::class, 'update'])->name('staff.update');
        Route::patch('/staff/{user}/toggle', [UserController::class, 'toggleActive'])->name('staff.toggle');

        // Supplier Management Module
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
});
