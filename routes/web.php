<?php

use Illuminate\Support\Facades\Route;

// Public Storefront UI (consumes /api/motor, /api/motor/meta/filters, /api/kontak)
Route::view('/', 'public.home')->name('home');
Route::view('/motor', 'public.catalog')->name('catalog.index');
Route::get('/motor/{id}', fn (int $id) => view('public.detail', ['motorId' => $id]))
    ->whereNumber('id')
    ->name('catalog.show');

// Admin Authentication UI (consumes POST /api/login)
Route::view('/login', 'auth.login')->name('web.login');

// Admin Panel UI (protected client-side via Sanctum Bearer token on /api/me & /api/admin/*)
Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::view('/motor', 'admin.motors.index')->name('motors.index');
    Route::get('/motor/create', fn () => view('admin.motors.form', ['motorId' => null]))->name('motors.create');
    Route::get('/motor/{id}/edit', fn (int $id) => view('admin.motors.form', ['motorId' => $id]))
        ->whereNumber('id')
        ->name('motors.edit');
});