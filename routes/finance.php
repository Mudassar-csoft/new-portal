<?php

use App\Http\Controllers\Finance\DashboardController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\PayeeController;
use App\Http\Controllers\Finance\PayrollController;
use App\Http\Controllers\Finance\ReceivableController;
use App\Http\Controllers\Finance\UtilityController;
use Illuminate\Support\Facades\Route;

Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/income', [DashboardController::class, 'incomeDetails'])->name('dashboard.income');
    Route::get('/dashboard/expense', [DashboardController::class, 'expenseDetails'])->name('dashboard.expense');
    Route::get('/dashboard/payables', [DashboardController::class, 'payablesDetails'])->name('dashboard.payables');
    Route::get('/dashboard/receivables', [DashboardController::class, 'receivablesDetails'])->name('dashboard.receivables');
    Route::get('/dashboard/net-cashflow', [DashboardController::class, 'netCashflowDetails'])->name('dashboard.netcashflow');

    Route::get('/expense/add', [ExpenseController::class, 'addForm'])->name('expense.add');
    Route::post('/expense/add', [ExpenseController::class, 'store'])->name('expense.store');
    Route::get('/expense/types', [ExpenseController::class, 'typesIndex'])->name('expense.types');
    Route::post('/expense/types', [ExpenseController::class, 'typesStore'])->name('expense.types.store');
    Route::get('/expense/rent', [ExpenseController::class, 'list'])->defaults('category', 'rent')->name('expense.rent');
    Route::get('/expense/marketing', [ExpenseController::class, 'list'])->defaults('category', 'marketing')->name('expense.marketing');
    Route::get('/expense/assets', [ExpenseController::class, 'list'])->defaults('category', 'asset')->name('expense.assets');
    Route::get('/expense/all', [ExpenseController::class, 'list'])->defaults('category', 'all')->name('expense.all');
    Route::post('/expense/{expense}/approve', [ExpenseController::class, 'approve'])->name('expense.approve');
    Route::post('/expense/{expense}/reject', [ExpenseController::class, 'reject'])->name('expense.reject');
    Route::post('/expense/{expense}/mark-paid', [ExpenseController::class, 'markPaid'])->name('expense.markPaid');

    Route::get('/expense/payroll', [PayrollController::class, 'index'])->name('expense.payroll');
    Route::post('/expense/payroll/generate', [PayrollController::class, 'generate'])->name('expense.payroll.generate');

    Route::get('/utility/pay', [UtilityController::class, 'payIndex'])->name('utility.pay');
    Route::post('/utility/pay', [UtilityController::class, 'payStore'])->name('utility.pay.store');
    Route::get('/utility/bills', [UtilityController::class, 'billsIndex'])->name('utility.bills');
    Route::post('/utility/bills', [UtilityController::class, 'billsStore'])->name('utility.bills.store');
    Route::get('/utility/types', [UtilityController::class, 'typesIndex'])->name('utility.types');
    Route::post('/utility/types', [UtilityController::class, 'typesStore'])->name('utility.types.store');

    Route::get('/payees', [PayeeController::class, 'index'])->name('payees');
    Route::post('/payees', [PayeeController::class, 'store'])->name('payees.store');
    Route::get('/payables', [ExpenseController::class, 'payables'])->name('payables');
    Route::get('/receivables', [ReceivableController::class, 'index'])->name('receivables');
    Route::post('/receivables/manual-invoice', [ReceivableController::class, 'store'])->name('receivables.store');
    Route::post('/receivables/{charge}/collect', [ReceivableController::class, 'collect'])->name('receivables.collect');
});
