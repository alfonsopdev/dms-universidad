<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

// Login view
Route::get('/login', function () {
    return view('app');
})->name('login');

// Google OAuth
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Rutas protegidas - Vue toma el control
Route::middleware(['auth'])->group(function () {
    Route::get('/{any}', function () {
        return view('app');
    })->where('any', '.*');
});