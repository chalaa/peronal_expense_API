<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BudgetController;

Route::post('/register', [AuthController::class,'register'])->name('register_user');
Route::post('/login', [AuthController::class,'login'])->name('login');
Route::post('/refresh',[AuthController::class,'refresh'])->name('refresh');

Route::middleware('auth:api')->group(function () {
    Route::get('/dashboard',[AuthController::class,'dashboard'])->name('dashboard');
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'getuser'])->name('me');

    // category
    Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('category.store');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('category.show');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');

    //transaction
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transaction.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transaction.store');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transaction.show');
    Route::put('/transactions/{id}', [TransactionController::class, 'update'])->name('transaction.update');
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transaction.destroy');

    // budget
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
    Route::get('/budget/{id}', [BudgetController::class, 'show'])->name('budget.show');
    Route::put('/budget/{id}', [BudgetController::class, 'update'])->name('budget.update');
    Route::delete('/budget/{id}', [BudgetController::class, 'destroy'])->name('budget.destroy');

    // income
    Route::get('/income', [IncomeController::class, 'index'])->name('income.index');
    Route::post('/income', [IncomeController::class, 'store'])->name('income.store');
    Route::get('/income/{id}', [IncomeController::class, 'show'])->name('income.show');
    Route::put('/income/{id}', [IncomeController::class, 'update'])->name('income.update');
    Route::delete('/income/{id}', [IncomeController::class, 'destroy'])->name('income.destroy');

    //expense
    Route::get('/expense', [ExpenseController::class, 'index'])->name('expense.index');
    Route::post('/expense', [ExpenseController::class, 'store'])->name('expense.store');
    Route::get('/expense/{id}', [ExpenseController::class, 'show'])->name('expense.show');
    Route::put('/expense/{id}', [ExpenseController::class, 'update'])->name('expense.update');
    Route::delete('/expense/{id}', [ExpenseController::class, 'destroy'])->name('expense.destroy');
});