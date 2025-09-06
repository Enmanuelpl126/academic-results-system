<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::with(['department', 'roles'])->get();
        $departments = Department::withCount('users')->get();

        return Inertia::render('Admin', [
            'initialUsers' => $users,
            'initialDepartments' => $departments
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
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/'],
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
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/'],
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

        Department::create($validated);

        return redirect()->route('admin.index')->with('success', 'Departamento creado correctamente.');
    }

    public function updateDepartment(Request $request, string $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $department->update($validated);

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
}
