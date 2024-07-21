<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class,'register'])->name('register_user');
Route::post('/login', [AuthController::class,'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::get('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/refresh',[AuthController::class,'refresh'])->name('refresh');
});