<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DatabaseTransferController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PulsaController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', [PosController::class, 'home'])->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin,cashier')->group(function (): void {
        Route::get('/pos', [PosController::class, 'pos'])->name('pos.index');
        Route::get('/stock', [PosController::class, 'stock'])->name('stock.index');
        Route::get('/pengeluaran', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/pulsa-elektrik', [PulsaController::class, 'index'])->name('pulsa.index');
        Route::get('/voucher-pulsa', [PulsaController::class, 'voucherIndex'])->name('voucher.index');
        Route::get('/riwayat-penjualan', [PulsaController::class, 'history'])->name('sales-history.index');

        Route::prefix('ajax')->name('ajax.')->group(function (): void {
            Route::get('/dashboard', [PosController::class, 'dashboard'])->name('dashboard');
            Route::get('/expenses', [ExpenseController::class, 'dashboard'])->name('expenses.dashboard');
            Route::get('/products', [PosController::class, 'products'])->name('products');
            Route::post('/products/{product}/stock', [PosController::class, 'addStock'])->name('products.stock');
            Route::post('/sales', [PosController::class, 'storeSale'])->name('sales.store');
            Route::get('/sales/history-data', [PosController::class, 'salesHistory'])->name('sales.history-data');
            Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
            Route::get('/pulsa/dashboard', [PulsaController::class, 'dashboard'])->name('pulsa.dashboard');
            Route::post('/pulsa/sales', [PulsaController::class, 'storeSale'])->name('pulsa.sales.store');
            Route::post('/pulsa/sales/{pulsaSale}/success', [PulsaController::class, 'markSuccess'])->name('pulsa.sales.success');
            Route::post('/pulsa/sales/{pulsaSale}/cancel', [PulsaController::class, 'cancelSale'])->name('pulsa.sales.cancel');
            Route::get('/voucher/dashboard', [PulsaController::class, 'voucherDashboard'])->name('voucher.dashboard');
            Route::post('/voucher/sales', [PulsaController::class, 'storeVoucherSale'])->name('voucher.sales.store');
            Route::post('/wallets', [PulsaController::class, 'storeWallet'])->name('wallets.store');
            Route::post('/wallets/{wallet}/topup', [PulsaController::class, 'topupWallet'])->name('wallets.topup');
        });
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/report', [PosController::class, 'report'])->name('report.index');
        Route::get('/report/pdf', [PosController::class, 'exportPdf'])->name('report.pdf');
        Route::get('/master-data', [PosController::class, 'masterData'])->name('master.index');
        Route::get('/master-data/produk-fisik', [PosController::class, 'masterPhysical'])->name('master.physical.index');
        Route::get('/master-data/produk-elektrik', [PosController::class, 'masterElectric'])->name('master.electric.index');
        Route::get('/master-data/dompet-saldo', [PulsaController::class, 'walletSettings'])->name('master.wallets.index');
        Route::get('/user-management', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/database/export', [DatabaseTransferController::class, 'export'])->name('database.export');
        Route::post('/database/import', [DatabaseTransferController::class, 'import'])->name('database.import');

        Route::prefix('ajax')->name('ajax.')->group(function (): void {
            Route::get('/report/data', [PosController::class, 'reportData'])->name('report.data');
            Route::post('/products', [PosController::class, 'storeProduct'])->name('products.store');
            Route::put('/products/{product}', [PosController::class, 'updateProduct'])->name('products.update');
            Route::post('/providers', [PulsaController::class, 'storeProvider'])->name('providers.store');
            Route::put('/wallets/{wallet}', [PulsaController::class, 'updateWallet'])->name('wallets.update');
            Route::get('/users/list', [UserManagementController::class, 'list'])->name('users.list');
            Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        });
    });
});
