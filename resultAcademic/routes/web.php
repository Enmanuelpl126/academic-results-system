<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/publicaciones', function () {
    return "Hello World";
})->name('publicaciones');

Route::get('/awards', function () {
    return Inertia::render('Awards');
})->name('awards');

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
