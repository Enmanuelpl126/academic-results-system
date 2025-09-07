<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * Búsqueda de usuarios para autocompletar (paginada)
     */
    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');
        $perPage = (int) $request->query('per_page', 15);
        $perPage = $perPage > 0 && $perPage <= 50 ? $perPage : 15;

        $query = User::query()
            ->select('id', 'name', 'department_id')
            ->where(function ($q2) {
                // Mostrar solo usuarios habilitados si la columna existe
                if (Schema::hasColumn('users', 'is_enabled')) {
                    $q2->where('is_enabled', true);
                }
            })
            ->orderBy('name');

        // Búsqueda por nombre
        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }

        // Sin restricciones por rol/departamento: se listan todos los usuarios habilitados

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => collect($paginator->items())->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->all(),
            'current_page' => $paginator->currentPage(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'total' => $paginator->total(),
        ]);
    }

    /**
     * Crea un nuevo usuario.
     * Espera los campos enviados por el frontend Admin.vue
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'ci' => ['nullable', 'digits:11', 'unique:users,ci'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            // Validar contra roles de Spatie por nombre
            'role' => ['required', 'exists:roles,name'],
            'teaching_category' => ['nullable', 'string', 'max:255'],
            'scientific_category' => ['nullable', 'string', 'max:255'],
            'professional_level' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->department_id = $validated['department_id'] ?? null;
        $user->ci = $validated['ci'] ?? null;
        $user->teaching_category = $validated['teaching_category'] ?? null;
        $user->scientific_category = $validated['scientific_category'] ?? null;
        $user->professional_level = $validated['professional_level'] ?? null;
        $user->save();

        // Asignar rol con Spatie Permissions
        $user->syncRoles([$validated['role']]);

        // Redirigir de vuelta (Inertia manejará el reload en el frontend)
        return redirect()->back()->with('success', 'Usuario creado correctamente');
    }
}
