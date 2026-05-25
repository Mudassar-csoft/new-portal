<?php

use App\Http\Controllers\Finance\DashboardController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\PayeeController;
use App\Http\Controllers\Finance\PayrollController;
use App\Http\Controllers\Finance\ReceivableController;
use App\Http\Controllers\Finance\RentController;
use App\Http\Controllers\Finance\UtilityController;
use Illuminate\Support\Facades\Route;

Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('permission:finance.dashboard.view')->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:finance.dashboard.view')->name('dashboard');
    Route::get('/dashboard/income', [DashboardController::class, 'incomeDetails'])->middleware('permission:finance.dashboard.view')->name('dashboard.income');
    Route::get('/dashboard/expense', [DashboardController::class, 'expenseDetails'])->middleware('permission:finance.dashboard.view')->name('dashboard.expense');
    Route::get('/dashboard/payables', [DashboardController::class, 'payablesDetails'])->middleware('permission:finance.dashboard.view')->name('dashboard.payables');
    Route::get('/dashboard/receivables', [DashboardController::class, 'receivablesDetails'])->middleware('permission:finance.dashboard.view')->name('dashboard.receivables');
    Route::get('/dashboard/net-cashflow', [DashboardController::class, 'netCashflowDetails'])->middleware('permission:finance.dashboard.view')->name('dashboard.netcashflow');

    Route::get('/expense/add', [ExpenseController::class, 'addForm'])->middleware('permission:finance.expense.create')->name('expense.add');
    Route::post('/expense/add', [ExpenseController::class, 'store'])->middleware('permission:finance.expense.create')->name('expense.store');
    Route::get('/expense/rent-meta', [ExpenseController::class, 'rentMeta'])->middleware('permission:finance.expense.create,finance.rent.view')->name('expense.rentMeta');
    Route::get('/expense/types', [ExpenseController::class, 'typesIndex'])->middleware('permission:finance.expense.view,finance.expense.create')->name('expense.types');
    Route::post('/expense/types', [ExpenseController::class, 'typesStore'])->middleware('permission:finance.expense.create')->name('expense.types.store');
    Route::get('/expense/rent', [ExpenseController::class, 'list'])->middleware('permission:finance.expense.view,finance.rent.view')->defaults('category', 'rent')->name('expense.rent');
    Route::get('/expense/marketing', [ExpenseController::class, 'list'])->middleware('permission:finance.expense.view')->defaults('category', 'marketing')->name('expense.marketing');
    Route::get('/expense/assets', [ExpenseController::class, 'list'])->middleware('permission:finance.expense.view')->defaults('category', 'asset')->name('expense.assets');
    Route::get('/expense/all', [ExpenseController::class, 'list'])->middleware('permission:finance.expense.view')->defaults('category', 'all')->name('expense.all');
    Route::post('/expense/{expense}/approve', [ExpenseController::class, 'approve'])->middleware('permission:finance.expense.update,finance.utility.update,finance.bill.update,finance.rent.update,finance.payroll.update')->name('expense.approve');
    Route::post('/expense/{expense}/reject', [ExpenseController::class, 'reject'])->middleware('permission:finance.expense.update,finance.utility.update,finance.bill.update,finance.rent.update,finance.payroll.update')->name('expense.reject');
    Route::post('/expense/{expense}/mark-paid', [ExpenseController::class, 'markPaid'])->middleware('permission:finance.expense.update,finance.utility.update,finance.bill.update,finance.rent.update,finance.payroll.update')->name('expense.markPaid');

    Route::get('/expense/payroll', [PayrollController::class, 'index'])->middleware('permission:finance.payroll.view,finance.payroll.create')->name('expense.payroll');
    Route::post('/expense/payroll/generate', [PayrollController::class, 'generate'])->middleware('permission:finance.payroll.create')->name('expense.payroll.generate');

    Route::get('/utility/pay', [UtilityController::class, 'payIndex'])->middleware('permission:finance.utility.view,finance.utility.create')->name('utility.pay');
    Route::post('/utility/pay', [UtilityController::class, 'payStore'])->middleware('permission:finance.utility.create')->name('utility.pay.store');
    Route::get('/utility/bills', [UtilityController::class, 'billsIndex'])->middleware('permission:finance.bill.view,finance.bill.create')->name('utility.bills');
    Route::post('/utility/bills', [UtilityController::class, 'billsStore'])->middleware('permission:finance.bill.create')->name('utility.bills.store');
    Route::patch('/utility/bills/{bill}', [UtilityController::class, 'billsUpdate'])->middleware('permission:finance.bill.update,finance.utility.update')->name('utility.bills.update');
    Route::get('/utility/lookup', [UtilityController::class, 'lookup'])->middleware('permission:finance.bill.view,finance.utility.view')->name('utility.lookup');
    Route::get('/utility/types', [UtilityController::class, 'typesIndex'])->middleware('permission:finance.utility.view,finance.utility.create')->name('utility.types');
    Route::post('/utility/types', [UtilityController::class, 'typesStore'])->middleware('permission:finance.utility.create')->name('utility.types.store');
    Route::get('/rent/setup', [RentController::class, 'index'])->middleware('permission:finance.rent.view,finance.rent.create')->name('rent.index');
    Route::post('/rent/setup', [RentController::class, 'store'])->middleware('permission:finance.rent.create')->name('rent.store');
    Route::patch('/rent/setup/{rent}', [RentController::class, 'update'])->middleware('permission:finance.rent.update')->name('rent.update');

    Route::get('/payees', [PayeeController::class, 'index'])->middleware('permission:finance.payee.view,finance.payee.create')->name('payees');
    Route::post('/payees', [PayeeController::class, 'store'])->middleware('permission:finance.payee.create')->name('payees.store');
    Route::get('/payables', [ExpenseController::class, 'payables'])->middleware('permission:finance.payable.view')->name('payables');
    Route::get('/receivables', [ReceivableController::class, 'index'])->middleware('permission:finance.receivable.view,finance.receivable.create')->name('receivables');
    Route::post('/receivables/manual-invoice', [ReceivableController::class, 'store'])->middleware('permission:finance.receivable.create')->name('receivables.store');
    Route::post('/receivables/{charge}/collect', [ReceivableController::class, 'collect'])->middleware('permission:finance.receivable.update')->name('receivables.collect');
});
