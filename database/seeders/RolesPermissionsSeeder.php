<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'documentos.ver',
            'documentos.crear',
            'documentos.editar',
            'documentos.eliminar',
            'documentos.aprobar',
            'documentos.descargar',
            'documentos.restaurar',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'reportes.ver',
            'configuracion.gestionar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'super_admin'   => $permissions,
            'administrador' => [
                'documentos.ver', 'documentos.crear',
                'documentos.editar', 'documentos.eliminar',
                'documentos.descargar', 'documentos.restaurar',
                'usuarios.ver', 'usuarios.crear',
                'usuarios.editar', 'reportes.ver',
            ],
            'jefe_area'   => [
                'documentos.ver', 'documentos.aprobar',
                'documentos.descargar', 'reportes.ver',
            ],
            'secretaria'  => [
                'documentos.ver', 'documentos.crear',
                'documentos.editar', 'documentos.descargar',
            ],
            'auditor'     => [
                'documentos.ver', 'documentos.descargar',
                'reportes.ver',
            ],
            'usuario'     => [
                'documentos.ver', 'documentos.descargar',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // Usuario super admin por defecto
        $admin = User::firstOrCreate(
            ['email' => 'admin@universidad.edu.pe'],
            [
                'name'     => 'Super Administrador',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');
    }
}