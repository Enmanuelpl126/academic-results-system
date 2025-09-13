<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Resultados - ver
            'view_all_results',
            'view_department_results',
            'view_own_results',
            // Resultados - CRUD
            'create_result',
            'edit_any_result',
            'edit_department_result',
            'edit_own_result', // reservado por si se quiere permitir luego
            'delete_any_result',
            'delete_department_result', // no asignado por ahora
            'delete_own_result', // no asignado por ahora
            // Usuarios
            'manage_users',
            'assign_roles',
            'view_all_users',
            // Departamentos
            'create_department',
            'edit_department',
            'delete_department',
            'view_all_departments',
            // Configuración
            'manage_roles_permissions',
            // Permiso agregado: administración del sistema (agrega/encapsula gestión de usuarios, departamentos y roles)
            'admin_system',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $directive = Role::firstOrCreate(['name' => 'directive', 'guard_name' => 'web']);
        $head = Role::firstOrCreate(['name' => 'head_dp', 'guard_name' => 'web']);
        $prof = Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);

        // Assign permissions to roles
        $admin->givePermissionTo([
            'view_all_results',
            'create_result',
            'edit_any_result',
            'delete_any_result',
            'manage_users',
            'assign_roles',
            'view_all_users',
            'create_department',
            'edit_department',
            'delete_department',
            'view_all_departments',
            'manage_roles_permissions',
            'admin_system',
        ]);

        $directive->syncPermissions([
            'view_all_results',
            'create_result',
            'edit_any_result',
            'view_all_users',
            'view_all_departments',
        ]);

        $head->syncPermissions([
            'view_department_results',
            'create_result',
            'edit_department_result',
            'view_all_departments',
        ]);

        $prof->syncPermissions([
            'view_own_results',
            'create_result',
        ]);

        // Cache again
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
