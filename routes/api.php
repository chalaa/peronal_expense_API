<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ExpenseController;


Route::post('/register', [AuthController::class,'register'])->name('register_user');
Route::post('/login', [AuthController::class,'login'])->name('login');
Route::post('/refresh',[AuthController::class,'refresh'])->name('refresh');

Route::middleware('auth:api')->group(function () {
    Route::get('/dashboard',[AuthController::class,'dashboard'])->name('dashboard');
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');

    // category
    Route::get('/category', [CategoryController::class, 'index'])->name('category.index');
    Route::post('/category', [CategoryController::class, 'store'])->name('category.store');
    Route::get('/category/{id}', [CategoryController::class, 'show'])->name('category.show');
    Route::put('/category/{id}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('/category/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');

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