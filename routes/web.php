<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // ¡No olvides importar esta clase!
use App\Http\Controllers\Auth\GoogleController;

// Vista de Login
Route::get('/login', function () {
    return view('app');
})->name('login');

// Google OAuth
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Rutas protegidas - Solo usuarios logueados
Route::middleware(['auth'])->group(function () {

    // 1. Ruta para que Vue sepa quién es el usuario y qué permisos tiene (Spatie)
    Route::get('/api/user', function (Request $request) {
        return response()->json([
            'user'        => $request->user(),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
        ]);
    });

    // 2. Catch-all: Vue Router toma el control de la navegación
    // Esta ruta DEBE ir al final del grupo para no interferir con otras
    Route::get('/{any}', function () {
        return view('app');
    })->where('any', '.*');
});