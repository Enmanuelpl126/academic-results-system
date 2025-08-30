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
        $recognitions = Recognition::with('users:id,name')
            ->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            })
            ->orderByDesc('date')
            ->paginate(10)
            ->through(function (Recognition $rec) {
                return [
                    'id' => $rec->id,
                    'name' => $rec->name,
                    'type' => $rec->type,
                    'date' => $rec->date?->toDateString(),
                    'description' => $rec->description,
                    'authors' => $rec->users->pluck('name')->all(),
                    'author_ids' => $rec->users->pluck('id')->all(),
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

        // Solo permitir actualizar reconocimientos asociados al usuario autenticado
        $recognition = Recognition::whereKey($id)
            ->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            })
            ->firstOrFail();

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

    /**
     * Elimina el reconocimiento si solo está asociado al usuario autenticado;
     * de lo contrario, desasocia al usuario actual del reconocimiento.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        $recognition = Recognition::whereKey($id)
            ->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->withCount('users')
            ->firstOrFail();

        if ($recognition->users_count > 1) {
            // Hay otros usuarios asociados: desasociar al actual
            $user->recognitions()->detach($recognition->id);
        } else {
            // Solo el usuario actual está asociado: eliminar el reconocimiento
            $recognition->delete();
        }

        return redirect()
            ->route('recognitions')
            ->with('success', 'Reconocimiento eliminado correctamente.');
    }
}
