<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::with(['department', 'roles'])->get();
        $departments = Department::withCount('users')->get();

        // Cargar roles y permisos para la interfaz de administración
        // Traducciones de roles para mostrar en la UI
        $roleLabels = [
            'admin' => 'Administrador',
            'directive' => 'Directivo',
            'head_dp' => 'Jefe de Departamento',
            'profesor' => 'Profesor',
        ];

        $roles = Role::withCount('users')->get()->map(function ($r) use ($roleLabels) {
            return [
                'id' => $r->name,
                'label' => $roleLabels[$r->name] ?? ucfirst(str_replace('_', ' ', $r->name)),
                'description' => '',
                'users_count' => $r->users()->count(),
                'permissions' => $r->permissions()->pluck('name'),
            ];
        });

        // Mapa de permisos a etiquetas amigables en español
        $permissionLabels = [
            'view_all_results' => 'Ver todos los resultados',
            'view_department_results' => 'Ver resultados del departamento',
            'view_own_results' => 'Ver mis resultados',
            'create_result' => 'Crear resultado',
            'edit_any_result' => 'Editar cualquier resultado',
            'edit_department_result' => 'Editar resultados del departamento',
            'edit_own_result' => 'Editar mis resultados',
            'delete_any_result' => 'Eliminar cualquier resultado',
            'delete_department_result' => 'Eliminar resultados del departamento',
            'delete_own_result' => 'Eliminar mis resultados',
            'manage_users' => 'Gestionar usuarios',
            'assign_roles' => 'Asignar roles',
            'view_all_users' => 'Ver todos los usuarios',
            'create_department' => 'Crear departamento',
            'edit_department' => 'Editar departamento',
            'delete_department' => 'Eliminar departamento',
            'view_all_departments' => 'Ver todos los departamentos',
            'manage_roles_permissions' => 'Gestionar roles y permisos',
        ];

        $permissions = Permission::all()->map(function ($p) use ($permissionLabels) {
            return [
                'name' => $p->name,
                'label' => $permissionLabels[$p->name] ?? ucfirst(str_replace('_', ' ', $p->name)),
            ];
        });

        return Inertia::render('Admin', [
            'initialUsers' => $users,
            'initialDepartments' => $departments,
            'initialRoles' => $roles,
            'initialPermissions' => $permissions,
        ]);
    }

    // Listar roles y permisos (retorna Inertia con datos para la UI)
    public function rolesIndex()
    {
        $roles = Role::withCount('users')->get()->map(function ($r) {
            return [
                'id' => $r->name,
                'label' => ucfirst(str_replace('_', ' ', $r->name)),
                'description' => '',
                'users_count' => $r->users()->count(),
                'permissions' => $r->permissions()->pluck('name'),
            ];
        });

        $permissionLabels = [
            'view_all_results' => 'Ver todos los resultados',
            'view_department_results' => 'Ver resultados del departamento',
            'view_own_results' => 'Ver mis resultados',
            'create_result' => 'Crear resultado',
            'edit_any_result' => 'Editar cualquier resultado',
            'edit_department_result' => 'Editar resultados del departamento',
            'edit_own_result' => 'Editar mis resultados',
            'delete_any_result' => 'Eliminar cualquier resultado',
            'delete_department_result' => 'Eliminar resultados del departamento',
            'delete_own_result' => 'Eliminar mis resultados',
            'manage_users' => 'Gestionar usuarios',
            'assign_roles' => 'Asignar roles',
            'view_all_users' => 'Ver todos los usuarios',
            'create_department' => 'Crear departamento',
            'edit_department' => 'Editar departamento',
            'delete_department' => 'Eliminar departamento',
            'view_all_departments' => 'Ver todos los departamentos',
            'manage_roles_permissions' => 'Gestionar roles y permisos',
        ];

        $permissions = Permission::all()->map(function ($p) use ($permissionLabels) {
            return [
                'name' => $p->name,
                'label' => $permissionLabels[$p->name] ?? ucfirst(str_replace('_', ' ', $p->name)),
            ];
        });

        return Inertia::render('Admin', [
            'initialRoles' => $roles,
            'initialPermissions' => $permissions,
        ]);
    }

    // Gestión de usuarios
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'ci' => ['required', 'digits:11', 'unique:users,ci'],
            'teaching_category' => ['nullable', 'string', Rule::in(['profesor_principal', 'profesor_ayudante', 'profesor_entrenador'])],
            'scientific_category' => ['nullable', 'string', Rule::in(['aspirante', 'investigador_agregado', 'investigador_titular'])],
            'professional_level' => ['required', 'string', Rule::in(['tecnico', 'especialista', 'obrero', 'licenciado', 'master', 'doctor_en_ciencias'])],
            'department_id' => ['nullable', 'exists:departments,id'],
            // Usar Spatie Roles por nombre (admin, directive, head_dp, profesor, ...)
            'role' => ['required', 'exists:roles,name'],
            // Complejidad: letras, números y caracteres especiales, mínimo 8
            // Usamos [0-9] y [^A-Za-z0-9] para máxima compatibilidad
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/'],
        ], [
            'password.regex' => 'La contraseña debe contener al menos una letra, un número y un carácter especial.',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->email_verified_at = now();
        $user->ci = $validated['ci'] ?? null;
        $user->teaching_category = $validated['teaching_category'] ?? null;
        $user->scientific_category = $validated['scientific_category'] ?? null;
        $user->professional_level = $validated['professional_level'] ?? null;
        $user->department_id = $validated['department_id'] ?? null;
        $user->save();

        // Asignar rol vía Spatie
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.index')->with('success', 'Usuario creado correctamente.');
    }

    public function updateUser(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'ci' => ['required', 'digits:11', Rule::unique('users', 'ci')->ignore($user->id)],
            'teaching_category' => ['nullable', 'string', Rule::in(['profesor_principal', 'profesor_ayudante', 'profesor_entrenador'])],
            'scientific_category' => ['nullable', 'string', Rule::in(['aspirante', 'investigador_agregado', 'investigador_titular'])],
            'professional_level' => ['required', 'string', Rule::in(['tecnico', 'especialista', 'obrero', 'licenciado', 'master', 'doctor_en_ciencias'])],
            'department_id' => ['nullable', 'exists:departments,id'],
            // Spatie role name
            'role' => ['required', 'exists:roles,name'],
            // Optional password change respecting same complexity
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/'],
        ], [
            'password.regex' => 'La contraseña debe contener al menos una letra, un número y un carácter especial.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->ci = $validated['ci'];
        $user->teaching_category = $validated['teaching_category'] ?? null;
        $user->scientific_category = $validated['scientific_category'] ?? null;
        $user->professional_level = $validated['professional_level'] ?? null;
        $user->department_id = $validated['department_id'] ?? null;
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        // Sync role via Spatie
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroyUser(string $id)
    {
        $user = User::findOrFail($id);
        // Evitar auto-deshabilitarse
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.index')->with('error', 'No puedes deshabilitar tu propia cuenta.');
        }
        // Evitar deshabilitar al último administrador habilitado
        if ($user->hasRole('admin')) {
            $enabledAdmins = Role::findByName('admin')->users()->where('is_enabled', true)->count();
            if ($enabledAdmins <= 1) {
                return redirect()->route('admin.index')->with('error', 'No se puede deshabilitar el último administrador habilitado.');
            }
        }
        // Deshabilitar en lugar de eliminar
        $user->is_enabled = false;
        $user->save();

        return redirect()->route('admin.index')->with('success', 'Usuario deshabilitado correctamente.');
    }

    // Habilitar/Deshabilitar usuario explícitamente
    public function updateUserStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'is_enabled' => ['required', 'boolean'],
        ]);

        $user = User::findOrFail($id);
        $next = (bool) $validated['is_enabled'];
        // Evitar auto-deshabilitarse
        if (!$next && auth()->id() === $user->id) {
            return redirect()->route('admin.index')->with('error', 'No puedes deshabilitar tu propia cuenta.');
        }
        // Evitar deshabilitar al último administrador habilitado
        if (!$next && $user->hasRole('admin')) {
            $enabledAdmins = Role::findByName('admin')->users()->where('is_enabled', true)->count();
            if ($enabledAdmins <= 1) {
                return redirect()->route('admin.index')->with('error', 'No se puede deshabilitar el último administrador habilitado.');
            }
        }
        $user->is_enabled = $next;
        $user->save();

        return redirect()->route('admin.index')->with('success', $user->is_enabled ? 'Usuario habilitado correctamente.' : 'Usuario deshabilitado correctamente.');
    }

    // Gestión de departamentos
    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Department::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.index')->with('success', 'Departamento creado correctamente.');
    }

    public function updateDepartment(Request $request, string $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $department->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.index')->with('success', 'Departamento actualizado correctamente.');
    }

    public function destroyDepartment(string $id)
    {
        $department = Department::findOrFail($id);
        
        // Verificar si hay usuarios asignados
        if ($department->users()->count() > 0) {
            return redirect()->route('admin.index')->with('error', 'No se puede eliminar un departamento que tiene usuarios asignados.');
        }

        $department->delete();

        return redirect()->route('admin.index')->with('success', 'Departamento eliminado correctamente.');
    }

    // Crear un rol nuevo y asignar permisos
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_\-]+$/', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.regex' => 'El nombre del rol solo puede contener letras minúsculas, números, guiones bajos o guiones.',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        // Limpiar caché de permisos de Spatie para que los cambios sean visibles de inmediato
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true, 'role' => $role], 201);
        }

        return redirect()->route('admin.index')->with('success', 'Rol creado correctamente.');
    }

    // Actualizar permisos de un rol
    public function updateRole(Request $request, string $id)
    {
        try {
            $role = Role::findByName($id);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => 'Rol no encontrado.'], 404);
            }
            return redirect()->route('admin.index')->with('error', 'Rol no encontrado.');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        // Limpiar caché de permisos de Spatie para que los cambios sean visibles de inmediato
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.index')->with('success', 'Permisos del rol actualizados.');
    }

    // Eliminar rol
    public function destroyRole(string $id)
    {
        try {
            $role = Role::findByName($id);
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->expectsJson()) {
                return response()->json(['error' => 'Rol no encontrado.'], 404);
            }
            return redirect()->route('admin.index')->with('error', 'Rol no encontrado.');
        }

        // Evitar eliminar rol admin
        if ($role->name === 'admin') {
            if (request()->wantsJson() || request()->expectsJson()) {
                return response()->json(['error' => 'No se puede eliminar el rol administrador.'], 403);
            }
            return redirect()->route('admin.index')->with('error', 'No se puede eliminar el rol administrador.');
        }

        // Reasignar usuarios con este rol a 'profesor' por defecto antes de eliminar
        $default = Role::where('name', 'profesor')->first();
        foreach ($role->users as $user) {
            $user->removeRole($role->name);
            if ($default) $user->assignRole($default->name);
        }

        $role->delete();

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.index')->with('success', 'Rol eliminado correctamente.');
    }
}
