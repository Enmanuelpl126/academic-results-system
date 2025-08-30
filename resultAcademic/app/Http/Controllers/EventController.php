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
        // Solo eventos asociados al usuario autenticado, con usuarios
        $events = Event::with('users:id,name')
            ->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            })
            ->orderByDesc('date')
            ->paginate(10)
            ->through(function (Event $event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'category' => $event->category,
                    'date' => $event->date?->toDateString(),
                    'description' => $event->description,
                    // Autores como arreglo para chips y author_ids para edición
                    'authors' => $event->users->pluck('name')->all(),
                    'author_ids' => $event->users->pluck('id')->all(),
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

    /**
     * Elimina un evento existente.
     */
    public function destroy(Event $event)
    {
        // Opcional: validar que el usuario autenticado esté asociado al evento
        // $this->authorize('delete', $event);

        // Eliminar relaciones y el propio evento
        $event->users()->detach();
        $event->delete();

        return redirect()
            ->route('events')
            ->with('success', 'Evento eliminado correctamente.');
    }
}
