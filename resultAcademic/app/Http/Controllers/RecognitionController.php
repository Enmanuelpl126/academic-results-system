<?php

namespace App\Http\Controllers;

use App\Models\Recognition;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;

class RecognitionController extends Controller
{
    /**
     * Muestra la página/listado de reconocimientos asociados al usuario autenticado.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Recognition::with('users:id,name');

        // Alcance de visibilidad: combinar permisos de ver/editar/eliminar
        if (
            $user->can('view_all_results') ||
            $user->can('edit_any_result') ||
            $user->can('delete_any_result')
        ) {
            // sin restricciones
        } elseif (
            $user->can('view_department_results') ||
            $user->can('edit_department_result') ||
            $user->can('delete_department_result')
        ) {
            $deptId = $user->department_id ?? optional($user->department)->id;
            if ($deptId) {
                $query->whereHas('users', function ($q) use ($deptId) {
                    $q->where('users.department_id', $deptId);
                });
            } else {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
        } else {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $recognitions = $query
            ->orderByDesc('date')
            ->paginate(10)
            ->through(function (Recognition $rec) use ($user) {
                return [
                    'id' => $rec->id,
                    'name' => $rec->name,
                    'type' => $rec->type,
                    'date' => $rec->date?->toDateString(),
                    'description' => $rec->description,
                    'authors' => $rec->users->pluck('name')->all(),
                    'author_ids' => $rec->users->pluck('id')->all(),
                    'can_edit' => $this->canEditRecognition($user, $rec),
                ];
            });

        return Inertia::render('Recognitions', [
            'recognitions' => $recognitions,
            'users' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Crea un nuevo reconocimiento y lo asocia a autores (incluye al usuario autenticado).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['integer', 'exists:users,id'],
        ]);

        if (!empty($validated['date'])) {
            $validated['date'] = Carbon::parse($validated['date'])->toDateString();
        }

        $recognition = Recognition::create([
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
            'date' => $validated['date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // Asociar autores seleccionados + garantizar inclusión del usuario autenticado
        $authorIds = collect($validated['authors'] ?? [])
            ->push($request->user()->id)
            ->unique()
            ->map(fn($id) => (int) $id)
            ->all();
        $recognition->users()->sync($authorIds);

        return redirect()
            ->route('recognitions')
            ->with('success', 'Reconocimiento creado correctamente.');
    }

    /**
     * Actualiza un reconocimiento existente (solo si está asociado al usuario autenticado)
     * y sincroniza sus autores, incluyendo al usuario autenticado.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['integer', 'exists:users,id'],
        ]);

        if (!empty($validated['date'])) {
            $validated['date'] = Carbon::parse($validated['date'])->toDateString();
        }

        $user = $request->user();
        $recognition = Recognition::with('users:id,department_id')->findOrFail($id);

        // Autorización de edición
        $allowed = false;
        if ($user->can('edit_any_result')) {
            $allowed = true;
        } elseif ($user->can('edit_department_result')) {
            $deptId = $user->department_id ?? optional($user->department)->id;
            $allowed = $deptId && $recognition->users->contains(fn($u) => (int)$u->department_id === (int)$deptId);
        } elseif ($user->can('edit_own_result')) {
            $allowed = $recognition->users->contains('id', $user->id);
        }
        abort_unless($allowed, 403);

        $recognition->update([
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
            'date' => $validated['date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // Asegurar inclusión del usuario autenticado y sincronizar autores
        $authorIds = collect($validated['authors'] ?? [])
            ->push($request->user()->id)
            ->unique()
            ->map(fn($uid) => (int) $uid)
            ->all();
        $recognition->users()->sync($authorIds);

        return redirect()
            ->route('recognitions')
            ->with('success', 'Reconocimiento actualizado correctamente.');
    }

    protected function canEditRecognition(User $user, Recognition $recognition): bool
    {
        if ($user->can('edit_any_result')) return true;
        if ($user->can('edit_department_result')) {
            $deptId = $user->department_id ?? optional($user->department)->id;
            if ($deptId) {
                $recognition->loadMissing('users:id,department_id');
                return $recognition->users->contains(fn($u) => (int)$u->department_id === (int)$deptId);
            }
        }
        if ($user->can('edit_own_result')) {
            $recognition->loadMissing('users:id');
            return $recognition->users->contains('id', $user->id);
        }
        return false;
    }

    /**
     * Eliminar reconocimiento con reglas:
     * - delete_any_result: elimina completamente.
     * - delete_department_result: elimina si algún autor pertenece al mismo departamento.
     * - delete_own_result: si hay múltiples autores, desasocia al usuario; si es único autor, elimina.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        $recognition = Recognition::with('users:id,department_id')
            ->findOrFail($id);

        $canAny = $user->can('delete_any_result');
        $canDept = $user->can('delete_department_result');
        $canOwn = $user->can('delete_own_result');

        $allowed = false;
        $mode = 'none';

        if ($canAny) {
            $allowed = true; $mode = 'any';
        } elseif ($canDept) {
            $deptId = $user->department_id ?? optional($user->department)->id;
            if ($deptId) {
                $allowed = $recognition->users->contains(fn($u) => (int)$u->department_id === (int)$deptId);
            }
            if ($allowed) $mode = 'dept';
        } elseif ($canOwn) {
            $allowed = $recognition->users->contains('id', $user->id);
            if ($allowed) $mode = 'own';
        }

        abort_unless($allowed, 403);

        if ($mode === 'own' && $recognition->users()->count() > 1) {
            $user->recognitions()->detach($recognition->id);
            return redirect()->route('recognitions')->with('success', 'Se retiró tu autoría del reconocimiento.');
        }

        // Eliminar completamente
        $recognition->users()->detach();
        $recognition->delete();

        return redirect()
            ->route('recognitions')
            ->with('success', 'Reconocimiento eliminado correctamente.');
    }
}
