<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Event;

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
                    // La UI actual muestra autores como string
                    'authors' => $event->users->pluck('name')->implode(', '),
                ];
            });

        return Inertia::render('Events', [
            'events' => $events,
        ]);
    }
}
