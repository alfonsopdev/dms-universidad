<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Unit; // Asegúrate de que existan estos modelos
use App\Models\DocumentType; 
use Illuminate\Support\Facades\Hash;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar caché de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir y crear Permisos
        $permissions = [
            'documentos.ver', 'documentos.crear', 'documentos.editar',
            'documentos.eliminar', 'documentos.aprobar', 'documentos.descargar',
            'documentos.restaurar', 'usuarios.ver', 'usuarios.crear',
            'usuarios.editar', 'usuarios.eliminar', 'reportes.ver',
            'configuracion.gestionar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Definir Roles y asignar sus permisos
        $roles = [
            'super_admin'   => $permissions,
            'administrador' => [
                'documentos.ver', 'documentos.crear', 'documentos.editar', 
                'documentos.eliminar', 'documentos.descargar', 'documentos.restaurar',
                'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'reportes.ver',
            ],
            'jefe_area'     => ['documentos.ver', 'documentos.aprobar', 'documentos.descargar', 'reportes.ver'],
            'secretaria'    => ['documentos.ver', 'documentos.crear', 'documentos.editar', 'documentos.descargar'],
            'auditor'       => ['documentos.ver', 'documentos.descargar', 'reportes.ver'],
            'usuario'       => ['documentos.ver', 'documentos.descargar'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // 4. Usuario Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@universidad.edu.pe'],
            [
                'name'      => 'Super Administrador',
                'password'  => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');

        // 5. NUEVO: Unidades base
        $units = [
            ['name' => 'Rectorado', 'code' => 'REC'],
            ['name' => 'Dirección de Bienestar Universitario', 'code' => 'DBU'],
            ['name' => 'Secretaría General', 'code' => 'SG'],
            ['name' => 'Oficina de Gestión de Calidad', 'code' => 'OGC'],
            ['name' => 'Unidad de Seguimiento al Graduado', 'code' => 'USG'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], $unit);
        }

        // 6. NUEVO: Tipos de documento
        $types = [
            ['name' => 'Resolución', 'code_prefix' => 'RES'],
            ['name' => 'Oficio',      'code_prefix' => 'OF'],
            ['name' => 'Informe',     'code_prefix' => 'INF'],
            ['name' => 'Contrato',    'code_prefix' => 'CON'],
            ['name' => 'Convenio',    'code_prefix' => 'CVN'],
            ['name' => 'Reglamento',  'code_prefix' => 'REG'],
            ['name' => 'Acta',        'code_prefix' => 'ACT'],
            ['name' => 'Expediente',  'code_prefix' => 'EXP'],
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(
                ['code_prefix' => $type['code_prefix']], $type
            );
        }
    }
}