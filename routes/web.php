<?php

use App\Http\Controllers\AdultoController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\FichaClinicaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatologiaController;
use App\Http\Controllers\CuidadorController;
use App\Http\Controllers\ConfigCuotaController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\AporteController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('personas', PersonaController::class);

    Route::resource('adultos', AdultoController::class)->except(['index']);
    Route::patch('adultos/{adulto}/baja', [AdultoController::class, 'baja'])->name('adultos.baja');
    Route::patch('adultos/{adulto}/reactivar', [AdultoController::class, 'reactivar'])->name('adultos.reactivar');

    Route::resource('socios', SocioController::class)->except(['index']);
    Route::resource('colaboradores', ColaboradorController::class)
        ->parameters(['colaboradores' => 'colaborador'])
        ->except(['index']);

    Route::resource('cuidadores', CuidadorController::class)
                    ->parameters(['cuidadores' => 'cuidador'])
                    ->only(['create', 'store', 'edit', 'update']);

    Route::prefix('adultos/{adulto}/ficha')
                    ->name('ficha.')
                    ->where([
                        'seccion' => 'patologias|controles|prestaciones|programados|credenciales|documentos|cuidadores',
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

        Route::prefix('cuotas')->name('cuotas.')->group(function () {
        Route::get('/', [CuotaController::class, 'index'])->name('index');
        Route::post('{cuota}/pagos', [PagoController::class, 'store'])->whereNumber('cuota')->name('pagos.store');

        Route::get('configuraciones', [ConfigCuotaController::class, 'index'])->name('configuraciones.index');
        Route::post('configuraciones', [ConfigCuotaController::class, 'store'])->name('configuraciones.store');
        Route::put('configuraciones/{configuracion}', [ConfigCuotaController::class, 'update'])->name('configuraciones.update');
        Route::delete('configuraciones/{configuracion}', [ConfigCuotaController::class, 'destroy'])->name('configuraciones.destroy');
        Route::post('configuraciones/{configuracion}/generar', [ConfigCuotaController::class, 'generar'])->name('configuraciones.generar');
    });

        Route::post('pagos/{pago}/anular', [PagoController::class, 'anular'])->whereNumber('pago')->name('pagos.anular');

        Route::get('aportes', [AporteController::class, 'index'])->name('aportes.index');
        Route::post('colaboradores/{colaborador}/aportes', [AporteController::class, 'store'])
            ->whereNumber('colaborador')->name('aportes.store');
        Route::post('aportes/{aporte}/anular', [AporteController::class, 'anular'])
            ->whereNumber('aporte')->name('aportes.anular');

});

require __DIR__.'/settings.php';
