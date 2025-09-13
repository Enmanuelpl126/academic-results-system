<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Event;
use Illuminate\Support\Carbon;
use App\Models\User;

class EventController extends Controller
{
    /**
     * Muestra la página/listado de eventos.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Event::with('users:id,name');

        if ($user->can('view_all_results')) {
            // sin restricciones
        } elseif ($user->can('view_department_results')) {
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

        $events = $query
            ->orderByDesc('date')
            ->paginate(10)
            ->through(function (Event $event) use ($user) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'category' => $event->category,
                    'date' => $event->date?->toDateString(),
                    'description' => $event->description,
                    // Autores como arreglo para chips y author_ids para edición
                    'authors' => $event->users->pluck('name')->all(),
                    'author_ids' => $event->users->pluck('id')->all(),
                    'can_edit' => $this->canEditEvent($user, $event),
                ];
            });

        return Inertia::render('Events', [
            'events' => $events,
            'users' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Crea un nuevo evento y lo asocia al usuario autenticado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['integer', 'exists:users,id'],
        ]);

        if (!empty($validated['date'])) {
            $validated['date'] = Carbon::parse($validated['date'])->toDateString();
        }

        $event = Event::create([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
            'date' => $validated['date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // Asociar autores seleccionados + garantizar inclusión del usuario autenticado
        $authorIds = collect($validated['authors'] ?? [])
            ->push($request->user()->id)
            ->unique()
            ->all();
        $event->users()->sync($authorIds);

        return redirect()
            ->route('events')
            ->with('success', 'Evento creado correctamente.');
    }

    /**
     * Actualiza un evento existente.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['integer', 'exists:users,id'],
        ]);

        if (!empty($validated['date'])) {
            $validated['date'] = Carbon::parse($validated['date'])->toDateString();
        }
        // Autorización de edición
        $user = $request->user();
        $event->loadMissing('users:id,department_id');
        $allowed = false;
        if ($user->can('edit_any_result')) {
            $allowed = true;
        } elseif ($user->can('edit_department_results')) {
            $deptId = $user->department_id ?? optional($user->department)->id;
            $allowed = $deptId && $event->users->contains(fn($u) => (int)$u->department_id === (int)$deptId);
        } elseif ($user->can('edit_own_result')) {
            $allowed = $event->users->contains('id', $user->id);
        }
        abort_unless($allowed, 403);

        $event->update([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
            'date' => $validated['date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // Sincronizar autores y garantizar inclusión del usuario autenticado
        $authorIds = collect($validated['authors'] ?? [])
            ->push($request->user()->id)
            ->unique()
            ->all();
        $event->users()->sync($authorIds);

        return redirect()
            ->route('events')
            ->with('success', 'Evento actualizado correctamente.');
    }

    protected function canEditEvent(User $user, Event $event): bool
    {
        if ($user->can('edit_any_result')) return true;
        if ($user->can('edit_department_results')) {
            $deptId = $user->department_id ?? optional($user->department)->id;
            if ($deptId) {
                $event->loadMissing('users:id,department_id');
                return $event->users->contains(fn($u) => (int)$u->department_id === (int)$deptId);
            }
        }
        if ($user->can('edit_own_result')) {
            $event->loadMissing('users:id');
            return $event->users->contains('id', $user->id);
        }
        return false;
    }

    /**
     * Elimina un evento con reglas de autorización:
     * - delete_any_result: eliminar totalmente.
     * - delete_department_result: eliminar si hay algún autor del mismo departamento.
     * - delete_own_result: si hay múltiples autores, desasocia al usuario; si es único autor, elimina.
     */
    public function destroy(Event $event)
    {
        $user = request()->user();
        $event->loadMissing('users:id,department_id');

        $canAny = $user->can('delete_any_result');
        $canDept = $user->can('delete_department_result');
        $canOwn = $user->can('delete_own_result');

        $allowed = false; $mode = 'none';
        if ($canAny) {
            $allowed = true; $mode = 'any';
        } elseif ($canDept) {
            $deptId = $user->department_id ?? optional($user->department)->id;
            if ($deptId) {
                $allowed = $event->users->contains(fn($u) => (int)$u->department_id === (int)$deptId);
            }
            if ($allowed) $mode = 'dept';
        } elseif ($canOwn) {
            $allowed = $event->users->contains('id', $user->id);
            if ($allowed) $mode = 'own';
        }

        abort_unless($allowed, 403);

        if ($mode === 'own' && $event->users()->count() > 1) {
            $user->events()->detach($event->id);
            return redirect()->route('events')->with('success', 'Se retiró tu autoría del evento.');
        }

        $event->users()->detach();
        $event->delete();

        return redirect()->route('events')->with('success', 'Evento eliminado correctamente.');
    }
}
