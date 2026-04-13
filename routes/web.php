<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleController;
// Importación de los nuevos controladores de la API
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentVersionController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\DocumentTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Vista de Login
/*Route::get('/login', function () {
    return view('app');
})->name('login');*/

Route::get('/login', function () {
    return view('auth.login'); // Aquí llama a tu nuevo Blade
})->name('login');

// Procesar el formulario de Login tradicional
Route::post('/login', [LoginController::class, 'authenticate']);

// Google OAuth
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Auth Middleware)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // 1. Información del usuario y permisos para Vue
    Route::get('/api/user', function (Request $request) {
        return response()->json([
            'user'        => $request->user(),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
        ]);
    });

    // 2. Grupo de API interna
    Route::prefix('api')->group(function () {
        // Usuarios
            Route::get('users/roles',    [UserController::class, 'roles']);
            Route::get('users/template', [UserController::class, 'template']);
            Route::post('users/import',  [UserController::class, 'import']);
            Route::apiResource('users',  UserController::class);
        
            // Roles
            Route::post('roles/assign',   [RoleController::class, 'assignToUser']);
            Route::apiResource('roles',   RoleController::class);

            // Permisos
            Route::get('permissions',     [PermissionController::class, 'index']);
            Route::post('permissions',    [PermissionController::class, 'store']);
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy']);                
        // Stats para dashboard
        Route::get('documents/stats', function () {
            return response()->json([
                'total'       => \App\Models\Document::active()->count(),
                'activos'     => \App\Models\Document::active()->where('status', 'activo')->count(),
                'en_revision' => \App\Models\Document::active()->where('status', 'en_revision')->count(),
                'obsoletos'   => \App\Models\Document::active()->where('status', 'obsoleto')->count(),
                'favoritos'   => \App\Models\Document::active()
                    ->whereHas('favorites', fn($q) => $q->where('user_id', auth()->id()))
                    ->count(),
            ]);
        });

        // Rutas específicas de documentos (deben ir ANTES del apiResource)
        Route::get('documents/trash',               [DocumentController::class, 'trash']);
        Route::get('documents/favorites',           [DocumentController::class, 'favorites']);
        Route::post('documents/{document}/restore', [DocumentController::class, 'restore']);
        Route::delete('documents/{document}/force', [DocumentController::class, 'forceDelete']);
        Route::post('documents/{document}/toggle-favorite', [DocumentController::class, 'toggleFavorite']);
        Route::get('documents/{document}/download', [DocumentController::class, 'download']);
        
        // Recursos principales
        Route::apiResource('documents', DocumentController::class);

        // Versiones de documentos
        Route::get('documents/{document}/versions',  [DocumentVersionController::class, 'index']);
        Route::post('documents/{document}/versions', [DocumentVersionController::class, 'store']);

        // Catálogos
        Route::apiResource('units',           UnitController::class);
        Route::apiResource('document-types',  DocumentTypeController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Vue SPA Catch-all
    |--------------------------------------------------------------------------
    | Esta ruta debe ser siempre la última dentro del grupo auth.
    */
    Route::get('/{any}', function () {
        return view('app');
    })->where('any', '.*');
});