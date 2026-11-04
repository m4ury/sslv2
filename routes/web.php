<?php

use App\Http\Controllers\AdultoController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\SocioController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('personas', PersonaController::class);
    Route::resource('adultos', AdultoController::class)->except(['index']);
    Route::resource('socios', SocioController::class)->except(['index']);
    Route::resource('colaboradores', ColaboradorController::class)->except(['index']);

});

require __DIR__.'/settings.php';
