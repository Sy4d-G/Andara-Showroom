<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicMotorController;
use Illuminate\Support\Facades\Route;

// Public Routes (Katalog & Informasi Showroom untuk Pelanggan & Umum)
Route::get('/motor', [PublicMotorController::class, 'index'])->name('api.motor.index');
Route::get('/motor/meta/filters', [PublicMotorController::class, 'filterMeta'])->name('api.motor.filters');
Route::get('/motor/{id}', [PublicMotorController::class, 'show'])->name('api.motor.show');
Route::get('/kontak', [PublicMotorController::class, 'kontak'])->name('api.kontak');

// Auth Routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
});
