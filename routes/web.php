<?php

use App\Http\Controllers\AdultoController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\FichaClinicaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatologiaController;


Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('personas', PersonaController::class);
    
    Route::resource('adultos', AdultoController::class)->except(['index']);
    Route::patch('adultos/{adulto}/baja', [AdultoController::class, 'baja'])->name('adultos.baja');
    Route::patch('adultos/{adulto}/reactivar', [AdultoController::class, 'reactivar'])->name('adultos.reactivar');

    Route::resource('socios', SocioController::class)->except(['index']);
    Route::resource('colaboradores', ColaboradorController::class)->except(['index']);

    Route::prefix('adultos/{adulto}/ficha')
    ->name('ficha.')
    ->where([
        'seccion' => 'controles|prestaciones|programados|credenciales|documentos',
        'id' => '[0-9]+',
        'documento' => '[0-9]+',
    ])
    ->group(function () {
        Route::get('/', [FichaClinicaController::class, 'show'])->name('show');
        Route::get('documentos/{documento}/descargar', [FichaClinicaController::class, 'descargar'])->name('descargar');
        Route::post('{seccion}', [FichaClinicaController::class, 'store'])->name('store');
        Route::put('{seccion}/{id}', [FichaClinicaController::class, 'update'])->name('update');
        Route::delete('{seccion}/{id}', [FichaClinicaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('catalogos')->name('catalogos.')->group(function () {
    Route::get('patologias', [PatologiaController::class, 'index'])->name('patologias.index');
    Route::post('patologias', [PatologiaController::class, 'store'])->name('patologias.store');
    Route::put('patologias/{patologia}', [PatologiaController::class, 'update'])->name('patologias.update');
    Route::delete('patologias/{patologia}', [PatologiaController::class, 'destroy'])->name('patologias.destroy');
});

});

require __DIR__.'/settings.php';
