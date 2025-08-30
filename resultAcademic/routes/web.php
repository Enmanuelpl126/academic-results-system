<?php

use App\Http\Controllers\AwardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\RecognitionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



// Awards protegidos: usa el controlador para obtener los datos del usuario autenticado
Route::get('/awards', [AwardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('awards');

Route::post('/awards', [AwardController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('awards.store');

Route::put('/awards/{id}', [AwardController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('awards.update');

Route::delete('/awards/{id}', [AwardController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('awards.destroy');

// Búsqueda de usuarios para autocompletar (combobox)
Route::get('/users/search', [UserController::class, 'search'])
    ->middleware(['auth', 'verified'])
    ->name('users.search');

Route::get('/recognitions', [RecognitionController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('recognitions');
Route::post('/recognitions', [RecognitionController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('recognitions.store');
Route::put('/recognitions/{id}', [RecognitionController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('recognitions.update');
Route::delete('/recognitions/{id}', [RecognitionController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('recognitions.destroy');
Route::get('/publications', [PublicationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('publications');
Route::post('/publications', [PublicationController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('publications.store');

Route::put('/publications/{publication}', [PublicationController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('publications.update');

Route::delete('/publications/{publication}', [PublicationController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('publications.destroy');

Route::get('/events', [EventController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('events');

Route::post('/events', [EventController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('events.store');

Route::put('/events/{event}', [EventController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('events.update');

Route::delete('/events/{event}', [EventController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('events.destroy');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
