<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Award;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Obtener premios del usuario autenticado, junto con los autores (usuarios asociados)
        $awards = Award::with('users:id,name')
            ->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            })
            ->orderByDesc('date')
            ->paginate(10)
            ->through(function ($award) {
                return [
                    'id' => $award->id,
                    'type' => $award->type,
                    'date' => Carbon::parse($award->date)->toDateString(),
                    'authors' => $award->users->pluck('name')->all(),
                    'author_ids' => $award->users->pluck('id')->all(),
                ];
            });

        // Renderizar la página Inertia correcta y pasar los datos al componente
        return Inertia::render('Awards', [
            'awards' => $awards,
            'users' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Awards/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'max:255',
                Rule::in(['Academia de Ciencias de Cuba', 'CITMA Provincial'])
            ],
            'date' => ['required', 'date'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['integer', 'exists:users,id'],
        ]);

        // Normalizar fecha a formato YYYY-MM-DD
        $validated['date'] = Carbon::parse($validated['date'])->toDateString();

        $award = Award::create([
            'type' => $validated['type'],
            'date' => $validated['date'],
        ]);

        // Asegurar que el usuario autenticado esté incluido en authors
        $authorIds = array_unique(array_map('intval', array_merge($validated['authors'], [$request->user()->id])));
        $award->users()->sync($authorIds);

        return redirect()
            ->route('awards')
            ->with('success', 'Premio creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $award = Award::findOrFail($id);

        return Inertia::render('Awards/Show', [
            'award' => $award,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'max:255',
                Rule::in(['Academia de Ciencias de Cuba', 'CITMA Provincial'])
            ],
            'date' => ['required', 'date'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['integer', 'exists:users,id'],
        ]);

        // Normalizar fecha a formato YYYY-MM-DD
        $validated['date'] = Carbon::parse($validated['date'])->toDateString();

        // Solo permitir actualizar premios asociados al usuario autenticado
        $award = Award::whereKey($id)
            ->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            })
            ->firstOrFail();

        $award->update([
            'type' => $validated['type'],
            'date' => $validated['date'],
        ]);

        // Asegurar inclusión del usuario autenticado y sincronizar autores
        $authorIds = array_unique(array_map('intval', array_merge($validated['authors'], [$request->user()->id])));
        $award->users()->sync($authorIds);

        return redirect()
            ->route('awards')
            ->with('success', 'Premio actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = request()->user();

        // Buscar premio asociado al usuario autenticado
        $award = Award::whereKey($id)
            ->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->withCount('users')
            ->firstOrFail();

        if ($award->users_count > 1) {
            // Si hay otros usuarios asociados, solo desasociar al actual
            $user->awards()->detach($award->id);
        } else {
            // Si solo está asociado al actual, eliminar el premio
            $award->delete();
        }

        return redirect()
            ->route('awards')
            ->with('success', 'Premio eliminado correctamente.');
    }
}
