<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;
use App\Models\Publication;
use App\Models\Magazine;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PublicationController extends Controller
{
    /**
     * Muestra el listado de publicaciones del usuario autenticado.
     * Incluye los 3 tipos soportados: journal, book, book_chapter.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $publications = Publication::with(['users:id,name', 'magazine', 'book', 'chapter'])
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->orderByDesc('date')
            ->paginate(10)
            ->through(function ($p) {
                $base = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'date' => Carbon::parse($p->date)->toDateString(),
                    'type' => $p->type,
                    'authors' => $p->users->pluck('name')->all(),
                    'author_ids' => $p->users->pluck('id')->all(),
                ];

                // Mapear detalles según tipo
                if ($p->type === 'Revista' && $p->magazine) {
                    $base = array_merge($base, [
                        'magazineName' => $p->magazine->name,
                        'number' => $p->magazine->number,
                        'volume' => $p->magazine->volume,
                        'doi' => $p->magazine->doi,
                        // 'url' opcional si existe en tu esquema
                    ]);
                } elseif ($p->type === 'Libro' && $p->book) {
                    $base = array_merge($base, [
                        'publisher' => $p->book->editorial,
                        'city' => $p->book->place,
                    ]);
                } elseif ($p->type === 'Capitulo de Libro' && $p->chapter) {
                    $base = array_merge($base, [
                        'bookName' => $p->chapter->book_name,
                        'bookAuthor' => $p->chapter->author,
                        'publisher' => $p->chapter->editorial,
                        'city' => $p->chapter->place,
                    ]);
                }

                return $base;
            });

        // Lista inicial de usuarios (id, name) para el combobox; se puede paginar en búsqueda
        $initialUsers = User::select('id', 'name')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return Inertia::render('Publications', [
            'publications' => $publications,
            'users' => $initialUsers,
        ]);
    }

    /**
     * Crea una nueva publicación y su registro relacionado según el tipo.
     */
    public function store(Request $request)
    {
        // Validación base
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:journal,book,book_chapter,Revista,Libro,Capitulo de Libro'],
            // Campos específicos por tipo (se validan después condicional)
            'number' => ['nullable', 'string', 'max:50'],
            'volume' => ['nullable', 'string', 'max:50'],
            'doi' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'bookName' => ['nullable', 'string', 'max:255'],
            'bookAuthor' => ['nullable', 'string', 'max:255'],
            'author_ids' => ['nullable', 'array'],
            'author_ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Normalizar tipo del formulario (inglés) a etiquetas en español utilizadas en el listado
        $typeMap = [
            'journal' => 'Revista',
            'book' => 'Libro',
            'book_chapter' => 'Capitulo de Libro',
            'Revista' => 'Revista',
            'Libro' => 'Libro',
            'Capitulo de Libro' => 'Capitulo de Libro',
        ];
        $normalizedType = $typeMap[$validated['type']] ?? $validated['type'];

        // Validación condicional por tipo
        if ($normalizedType === 'Revista') {
            $request->validate([
                'number' => ['required', 'string', 'max:50'],
                'volume' => ['required', 'string', 'max:50'],
            ]);
        } elseif ($normalizedType === 'Libro') {
            $request->validate([
                'publisher' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
            ]);
        } elseif ($normalizedType === 'Capitulo de Libro') {
            $request->validate([
                'bookName' => ['required', 'string', 'max:255'],
                'bookAuthor' => ['required', 'string', 'max:255'],
                'publisher' => ['required', 'string', 'max:255'],
            ]);
        }

        // Crear publicación base
        $publication = Publication::create([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'type' => $normalizedType,
            'description' => $request->input('description'),
        ]);

        // Crear registro relacionado con clave compartida (id)
        if ($normalizedType === 'Revista') {
            Magazine::create([
                'id' => $publication->id,
                'name' => $request->input('magazineName'), // nombre de la revista
                'number' => $request->input('number'),
                'volume' => $request->input('volume'),
                'doi' => $request->input('doi'),
            ]);
        } elseif ($normalizedType === 'Libro') {
            Book::create([
                'id' => $publication->id,
                'editorial' => $request->input('publisher'),
                'place' => $request->input('city'),
            ]);
        } elseif ($normalizedType === 'Capitulo de Libro') {
            Chapter::create([
                'id' => $publication->id,
                'book_name' => $request->input('bookName'),
                'author' => $request->input('bookAuthor'),
                'editorial' => $request->input('publisher'),
                'place' => $request->input('city'),
            ]);
        }

        // Asociar autores: si no se envían, adjuntar usuario autenticado
        $authorIds = $request->input('author_ids');
        if (is_array($authorIds) && count($authorIds) > 0) {
            $publication->users()->sync($authorIds);
        } else {
            $publication->users()->sync([$request->user()->id]);
        }

        return redirect()
            ->route('publications')
            ->with('success', 'Publicación creada correctamente');
    }

    /**
     * Actualiza una publicación existente y su registro relacionado según el tipo.
     */
    public function update(Request $request, Publication $publication)
    {
        // Validación base
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:journal,book,book_chapter,Revista,Libro,Capitulo de Libro'],
            'number' => ['nullable', 'string', 'max:50'],
            'volume' => ['nullable', 'string', 'max:50'],
            'doi' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'bookName' => ['nullable', 'string', 'max:255'],
            'bookAuthor' => ['nullable', 'string', 'max:255'],
            'author_ids' => ['nullable', 'array'],
            'author_ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Normalizar tipo
        $typeMap = [
            'journal' => 'Revista',
            'book' => 'Libro',
            'book_chapter' => 'Capitulo de Libro',
            'Revista' => 'Revista',
            'Libro' => 'Libro',
            'Capitulo de Libro' => 'Capitulo de Libro',
        ];
        $normalizedType = $typeMap[$validated['type']] ?? $validated['type'];

        // Actualizar publicación base
        $publication->update([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'type' => $normalizedType,
            'description' => $request->input('description'),
        ]);

        // Asegurar relaciones coherentes por tipo (el esquema usa misma PK id)
        // Borrar/limpiar otras relaciones si cambia el tipo
        if ($normalizedType !== 'Revista' && $publication->magazine) {
            $publication->magazine()->delete();
        }
        if ($normalizedType !== 'Libro' && $publication->book) {
            $publication->book()->delete();
        }
        if ($normalizedType !== 'Capitulo de Libro' && $publication->chapter) {
            $publication->chapter()->delete();
        }

        if ($normalizedType === 'Revista') {
            // Crear o actualizar Magazine
            Magazine::updateOrCreate(
                ['id' => $publication->id],
                [
                    'name' => $request->input('magazineName'),
                    'number' => $request->input('number'),
                    'volume' => $request->input('volume'),
                    'doi' => $request->input('doi'),
                ]
            );
        } elseif ($normalizedType === 'Libro') {
            Book::updateOrCreate(
                ['id' => $publication->id],
                [
                    'editorial' => $request->input('publisher'),
                    'place' => $request->input('city'),
                ]
            );
        } elseif ($normalizedType === 'Capitulo de Libro') {
            Chapter::updateOrCreate(
                ['id' => $publication->id],
                [
                    'book_name' => $request->input('bookName'),
                    'author' => $request->input('bookAuthor'),
                    'editorial' => $request->input('publisher'),
                    'place' => $request->input('city'),
                ]
            );
        }

        // Autores
        $authorIds = $request->input('author_ids');
        if (is_array($authorIds) && count($authorIds) > 0) {
            $publication->users()->sync($authorIds);
        } else {
            $publication->users()->sync([$request->user()->id]);
        }

        return redirect()
            ->route('publications')
            ->with('success', 'Publicación actualizada correctamente');
    }

    /**
     * Elimina una publicación y sus registros relacionados.
     */
    public function destroy(Publication $publication)
    {
        DB::transaction(function () use ($publication) {
            // Eliminar relaciones por tipo si existen
            if ($publication->magazine) {
                $publication->magazine()->delete();
            }
            if ($publication->book) {
                $publication->book()->delete();
            }
            if ($publication->chapter) {
                $publication->chapter()->delete();
            }

            // Desasociar autores (pivot)
            $publication->users()->detach();

            // Eliminar publicación
            $publication->delete();
        });

        return redirect()
            ->route('publications')
            ->with('success', 'Publicación eliminada correctamente');
    }
}
