<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigCuota;
use App\Services\GeneradorCuotas;
use Inertia\Inertia;


class ConfigCuotaController extends Controller
{
    public function index()
    {
        return Inertia::render('cuotas/Configuraciones', [
            'configuraciones' => ConfigCuota::withCount('cuotas')->orderByDesc('fecha_inicio')->get(),
        ]);
    }

    public function store(Request $request)
    {
        ConfigCuota::create($this->validar($request));

        return back()->with('success', 'Configuración creada.');
    }

    public function update(Request $request, ConfigCuota $configuracion)
    {
        $configuracion->update($this->validar($request, true));

        return back()->with('success', 'Configuración actualizada. Las cuotas ya generadas mantienen su monto.');
    }

    public function destroy(ConfigCuota $configuracion)
    {
        if ($configuracion->cuotas()->exists()) {
            return back()->with('error', 'No se puede eliminar: ya tiene cuotas generadas. Márcala como inactiva.');
        }

        $configuracion->delete();

        return back()->with('success', 'Configuración eliminada.');
    }

    public function generar(ConfigCuota $configuracion, GeneradorCuotas $generador)
    {
        if (!$configuracion->activa) {
            return back()->with('error', 'La configuración está inactiva.');
        }

        $r = $generador->generar($configuracion);

        return back()->with('success', "Cuotas generadas: {$r['creadas']} nuevas, {$r['existentes']} ya existían.");
    }

    private function validar(Request $request, bool $editando = false): array
    {
        return $request->validate([
            'nombre_cuota' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'dia_vencimiento' => ['required', 'integer', 'between:1,28'],
            ...($editando ? ['activa' => ['required', 'boolean']] : []),
        ]);
    }
}
