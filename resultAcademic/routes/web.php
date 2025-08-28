<?php

use App\Http\Controllers\AwardController;
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

Route::inertia('/recognitions', 'Recognitions')->name('recognitions');
Route::inertia('/publications', 'Publications')->name('publications');

Route::get('/events', function () {
    return Inertia::render('Events');
})->name('events');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
