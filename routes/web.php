<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Authenticated Routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Central Role-Aware Workspace / Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Staff operations console (owner + staff) — live selling / POS / triage
    Route::get('/console', [DashboardController::class, 'console'])->name('staff.workspace');

    // In-app help / onboarding guide
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');

    // Lazily-loaded detail panels (order / receipt / audit)
    Route::get('/panel/{type}/{id}', [PanelController::class, 'show'])
        ->where('type', 'order|receipt|audit')
        ->name('panels.show');

    // Inventory & Triage Module
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::patch('/items/{item}/triage', [ItemController::class, 'triage'])->name('items.triage');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');

    // Batches Module
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
    Route::put('/batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
    Route::delete('/batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
    Route::post('/batches/{id}/restore', [BatchController::class, 'restore'])->name('batches.restore');

    // Orders & Walk-in POS Module
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::post('/orders/award', [OrderController::class, 'award'])->name('orders.award');
    Route::post('/orders/pos-checkout', [OrderController::class, 'posCheckout'])->name('orders.pos-checkout');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/release', [OrderController::class, 'release'])->name('orders.release');

    // Payment Verification Module
    Route::post('/payments/verify', [PaymentController::class, 'verify'])->name('payments.verify');

    // Deliveries & Fulfillment Module
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::put('/deliveries/{delivery}', [DeliveryController::class, 'update'])->name('deliveries.update');

    // Operating Expenses Module
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/expenses/{id}/restore', [ExpenseController::class, 'restore'])->name('expenses.restore');

    // Owner-Only Protected Routes
    Route::middleware('role:owner')->group(function () {
        // Security & Audit Log (accessible from the owner workspace, not in nav)
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');

        // Financial & Profit Reports Module
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'exportExcel'])->name('reports.export');
        Route::get('/reports/export-all', [ReportController::class, 'exportAllExcel'])->name('reports.export-all');

        // Data Backup & Recovery
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/download', [BackupController::class, 'downloadCurrent'])->name('backups.download');
        Route::post('/backups/import', [BackupController::class, 'import'])->name('backups.import');
        Route::get('/backups/{file}/download', [BackupController::class, 'download'])->name('backups.file')->where('file', '[A-Za-z0-9_\-\.]+');
        Route::post('/backups/{file}/restore', [BackupController::class, 'restore'])->name('backups.restore')->where('file', '[A-Za-z0-9_\-\.]+');
        Route::delete('/backups/{file}', [BackupController::class, 'destroy'])->name('backups.destroy')->where('file', '[A-Za-z0-9_\-\.]+');
        Route::post('/backups/{file}/undo', [BackupController::class, 'undo'])->name('backups.undo')->where('file', '[A-Za-z0-9_\-\.]+');

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
        Route::post('/suppliers/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
    });
});
