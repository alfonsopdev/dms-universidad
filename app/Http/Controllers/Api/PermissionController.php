<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Listar todos los permisos agrupados por módulo
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('name')->get();

        // Agrupar por módulo (prefijo antes del punto)
        $grouped = $permissions->groupBy(function($p) {
            return explode('.', $p->name)[0];
        })->map(function($group, $module) {
            return [
                'module'      => $module,
                'permissions' => $group->values(),
            ];
        })->values();

        return response()->json([
            'all'     => $permissions,
            'grouped' => $grouped,
        ]);
    }

    /**
     * Crear permiso
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name|max:100',
        ]);

        $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => 'web',
        ]);

        return response()->json($permission, 201);
    }

    /**
     * Eliminar permiso
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();
        return response()->json(['message' => 'Permiso eliminado.']);
    }
}