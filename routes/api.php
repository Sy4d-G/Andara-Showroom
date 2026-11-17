<?php

use App\Http\Controllers\Api\AdminMotorController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicMotorController;
use App\Http\Controllers\Api\SalesTransactionController;
use Illuminate\Support\Facades\Route;

// Public Routes (Katalog & Informasi Showroom untuk Pelanggan & Umum)
Route::get('/motor', [PublicMotorController::class, 'index'])->name('api.motor.index');
Route::get('/motor/meta/filters', [PublicMotorController::class, 'filterMeta'])->name('api.motor.filters');
Route::get('/motor/{id}', [PublicMotorController::class, 'show'])->name('api.motor.show');
Route::get('/kontak', [PublicMotorController::class, 'kontak'])->name('api.kontak');

// Auth Routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Protected Admin Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Admin Motor CRUD
    Route::apiResource('admin/motor', AdminMotorController::class)->names('api.admin.motor');
    Route::post('admin/motor/{id}', [AdminMotorController::class, 'update'])->name('api.admin.motor.post_update');

    // Sales Transactions
    Route::get('admin/transaksi/rekap', [SalesTransactionController::class, 'rekap'])->name('api.admin.transaksi.rekap');
    Route::get('admin/transaksi', [SalesTransactionController::class, 'index'])->name('api.admin.transaksi.index');
    Route::post('admin/transaksi', [SalesTransactionController::class, 'store'])->name('api.admin.transaksi.store');
    Route::get('admin/transaksi/{id}', [SalesTransactionController::class, 'show'])->name('api.admin.transaksi.show');
});
