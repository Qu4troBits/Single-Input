<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\BankAccountsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\ReportsController;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->middleware('throttle:10,1')->name('two-factor.challenge.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::get('/two-factor/setup', [TwoFactorController::class, 'showSetup'])->name('two-factor.setup');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');

    Route::get('/transactions', [TransactionsController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionsController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionsController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/edit', [TransactionsController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionsController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionsController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/transactions/{transaction}/mark-as-paid', [TransactionsController::class, 'markAsPaid'])->name('transactions.markAsPaid');
    Route::post('/transactions/{transaction}/mark-as-cancelled', [TransactionsController::class, 'markAsCancelled'])->name('transactions.markAsCancelled');

    Route::resource('bank-accounts', \App\Http\Controllers\BankAccountsController::class)->except(['show']);
    Route::resource('categories', \App\Http\Controllers\CategoriesController::class)->except(['show']);
    
    // Relatórios
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/monthly/{yearMonth}', [ReportsController::class, 'showMonthlyDre'])->name('monthly.dre');
        Route::get('/quarterly/{year}/{quarter}', [ReportsController::class, 'showQuarterlyDre'])->name('quarterly.dre');
        Route::get('/yearly/{year}', [ReportsController::class, 'showYearlyDre'])->name('yearly.dre');
        Route::post('/custom', [ReportsController::class, 'generateCustomDre'])->name('custom.dre');
        Route::get('/profit-margin-trend', [ReportsController::class, 'showProfitMarginTrend'])->name('profit.margin.trend');
        Route::get('/revenue-by-category', [ReportsController::class, 'showRevenueByCategory'])->name('revenue.by.category');
        Route::get('/expenses-by-category', [ReportsController::class, 'showExpensesByCategory'])->name('expenses.by.category');
    });

    // Conciliação Bancária
    Route::prefix('bank-reconciliation')->name('bank-reconciliation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BankReconciliationController::class, 'index'])->name('index');
        Route::get('/{bankAccountId}', [\App\Http\Controllers\BankReconciliationController::class, 'show'])->name('show');
        Route::get('/{bankAccountId}/import', [\App\Http\Controllers\BankReconciliationController::class, 'importForm'])->name('import.form');
        Route::post('/{bankAccountId}/import', [\App\Http\Controllers\BankReconciliationController::class, 'import'])->name('import');
        Route::get('/{bankAccountId}/reconcile', [\App\Http\Controllers\BankReconciliationController::class, 'reconcileForm'])->name('reconcile.form');
        Route::post('/{bankAccountId}/reconcile', [\App\Http\Controllers\BankReconciliationController::class, 'reconcile'])->name('reconcile');
    });
});
