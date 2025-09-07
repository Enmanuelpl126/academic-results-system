<?php

use App\Http\Controllers\AwardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\RecognitionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('publications')
        : redirect()->route('login');
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
    ->middleware(['auth', 'verified', 'permission:delete_any_result'])
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
    ->middleware(['auth', 'verified', 'permission:delete_any_result'])
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
    ->middleware(['auth', 'verified', 'permission:delete_any_result'])
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
    ->middleware(['auth', 'verified', 'permission:delete_any_result'])
    ->name('events.destroy');

Route::get('dashboard', function () {
    return redirect()->route('publications');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de administración
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard de administración: permitir a quien puede ver usuarios o departamentos
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])
        ->middleware(['permission:view_all_users|view_all_departments'])
        ->name('index');

    // Gestión de usuarios (solo administradores con manage_users)
    Route::post('/users', [App\Http\Controllers\AdminController::class, 'storeUser'])
        ->middleware(['permission:manage_users'])
        ->name('users.store');
    Route::put('/users/{id}', [App\Http\Controllers\AdminController::class, 'updateUser'])
        ->middleware(['permission:manage_users'])
        ->name('users.update');
    Route::put('/users/{id}/status', [App\Http\Controllers\AdminController::class, 'updateUserStatus'])
        ->middleware(['permission:manage_users'])
        ->name('users.status');
    Route::delete('/users/{id}', [App\Http\Controllers\AdminController::class, 'destroyUser'])
        ->middleware(['permission:manage_users'])
        ->name('users.destroy');

    // Gestión de departamentos (solo administradores)
    Route::post('/departments', [App\Http\Controllers\DepartmentController::class, 'store'])
        ->middleware(['permission:create_department'])
        ->name('departments.store');
    Route::put('/departments/{id}', [App\Http\Controllers\DepartmentController::class, 'update'])
        ->middleware(['permission:edit_department'])
        ->name('departments.update');
    Route::delete('/departments/{id}', [App\Http\Controllers\DepartmentController::class, 'destroy'])
        ->middleware(['permission:delete_department'])
        ->name('departments.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
