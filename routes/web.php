<?php

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
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProfesionController;



Route::inertia('/', 'Welcome')->name('home');

Route::inertia('dashboard', 'Dashboard')->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // ---------- Personas ----------
// El orden importa: "create" debe registrarse antes que "show" (personas/{persona})
Route::resource('personas', PersonaController::class)
    ->except(['index', 'show'])->middleware('can:personas.gestionar');
Route::resource('personas', PersonaController::class)
    ->only(['index', 'show'])->middleware('can:personas.ver');

// ---------- Roles: socios, colaboradores, cuidadores ----------
Route::middleware('can:roles.gestionar')->group(function () {
    // Mueve aquí tus Route::resource actuales de estos roles, con sus opciones
    // (y las rutas de baja/reactivar, si las implementaste).
    /* Route::resource('socios', SocioController::class); */
    Route::resource('socios', SocioController::class)->except(['index']);
    Route::patch('socios/{socio}/baja', [SocioController::class, 'baja'])->name('socios.baja');
    Route::patch('socios/{socio}/reactivar', [SocioController::class, 'reactivar'])->name('socios.reactivar');
    Route::resource('colaboradores', ColaboradorController::class)
        ->parameters(['colaboradores' => 'colaborador']);
    Route::resource('cuidadores', CuidadorController::class)
        ->parameters(['cuidadores' => 'cuidador'])
        ->only(['create', 'store', 'edit', 'update']);
});

// ---------- Ficha clínica ----------
Route::prefix('socios/{socio}/ficha')
    ->name('ficha.')
    ->where([
        'seccion' => 'patologias|controles|prestaciones|programados|credenciales|documentos|cuidadores',
        'id' => '[0-9]+',
        'documento' => '[0-9]+',
    ])
    ->group(function () {
        Route::middleware('can:ficha.ver')->group(function () {
            Route::get('/', [FichaClinicaController::class, 'show'])->name('show');
            Route::get('documentos/{documento}/descargar', [FichaClinicaController::class, 'descargar'])->name('descargar');
        });

        // Escritura: cada sección exige su permiso dentro del controlador
        Route::post('{seccion}', [FichaClinicaController::class, 'store'])->name('store');
        Route::put('{seccion}/{id}', [FichaClinicaController::class, 'update'])->name('update');
        Route::delete('{seccion}/{id}', [FichaClinicaController::class, 'destroy'])->name('destroy');
    });

        // ---------- Catálogo de patologías ----------
        Route::prefix('catalogos')->name('catalogos.')->middleware('can:patologias.catalogo')->group(function () {
            Route::get('patologias', [PatologiaController::class, 'index'])->name('patologias.index');
            Route::post('patologias', [PatologiaController::class, 'store'])->name('patologias.store');
            Route::put('patologias/{patologia}', [PatologiaController::class, 'update'])->name('patologias.update');
            Route::delete('patologias/{patologia}', [PatologiaController::class, 'destroy'])->name('patologias.destroy');
        });

        // ---------- Cuotas ----------
        Route::prefix('cuotas')->name('cuotas.')->group(function () {
            Route::get('/', [CuotaController::class, 'index'])->middleware('can:cuotas.ver')->name('index');
            Route::post('{cuota}/pagos', [PagoController::class, 'store'])
                ->middleware('can:cuotas.pagar')->whereNumber('cuota')->name('pagos.store');

            Route::middleware('can:cuotas.configurar')->group(function () {
                Route::get('configuraciones', [ConfigCuotaController::class, 'index'])->name('configuraciones.index');
                Route::post('configuraciones', [ConfigCuotaController::class, 'store'])->name('configuraciones.store');
                Route::put('configuraciones/{configuracion}', [ConfigCuotaController::class, 'update'])->name('configuraciones.update');
                Route::delete('configuraciones/{configuracion}', [ConfigCuotaController::class, 'destroy'])->name('configuraciones.destroy');
                Route::post('configuraciones/{configuracion}/generar', [ConfigCuotaController::class, 'generar'])->name('configuraciones.generar');
            });
        });
        Route::post('pagos/{pago}/anular', [PagoController::class, 'anular'])
            ->middleware('can:cuotas.pagar')->whereNumber('pago')->name('pagos.anular');

        // ---------- Aportes ----------
        Route::get('aportes', [AporteController::class, 'index'])->middleware('can:aportes.ver')->name('aportes.index');
        Route::post('colaboradores/{colaborador}/aportes', [AporteController::class, 'store'])
            ->middleware('can:aportes.registrar')->whereNumber('colaborador')->name('aportes.store');
        Route::post('aportes/{aporte}/anular', [AporteController::class, 'anular'])
            ->middleware('can:aportes.registrar')->whereNumber('aporte')->name('aportes.anular');

        // ---------- Administración ----------
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::middleware('can:usuarios.gestionar')->group(function () {
                Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
                Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
                Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
            });

            Route::middleware('can:profesiones.gestionar')->group(function () {
                Route::get('profesiones', [ProfesionController::class, 'index'])->name('profesiones.index');
                Route::post('profesiones', [ProfesionController::class, 'store'])->name('profesiones.store');
                Route::put('profesiones/{profesion}', [ProfesionController::class, 'update'])->name('profesiones.update');
                Route::delete('profesiones/{profesion}', [ProfesionController::class, 'destroy'])->name('profesiones.destroy');
            });
        });

});

require __DIR__.'/settings.php';
