<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Listar roles con sus permisos
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return response()->json($roles);
    }

    /**
     * Crear rol
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|unique:roles,name|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'), 201);
    }

    /**
     * Ver rol con permisos y usuarios
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');
        $role->loadCount('users');

        return response()->json($role);
    }

    /**
     * Actualizar rol y sus permisos
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'name'          => "required|string|unique:roles,name,{$role->id}|max:100",
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // No permitir editar roles del sistema
        if (in_array($role->name, ['super_admin'])) {
            return response()->json(['message' => 'No se puede modificar este rol del sistema.'], 403);
        }

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return response()->json($role->load('permissions'));
    }

    /**
     * Eliminar rol
     */
    public function destroy(Role $role): JsonResponse
    {
        if (in_array($role->name, ['super_admin', 'administrador', 'usuario'])) {
            return response()->json(['message' => 'No se puede eliminar este rol del sistema.'], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'No se puede eliminar un rol asignado a usuarios.'], 422);
        }

        $role->delete();
        return response()->json(['message' => 'Rol eliminado.']);
    }

    /**
     * Asignar roles a un usuario
     */
    public function assignToUser(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles'   => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $user->syncRoles($request->roles);

        return response()->json([
            'message' => 'Roles asignados correctamente.',
            'user'    => $user->load('roles'),
        ]);
    }
}