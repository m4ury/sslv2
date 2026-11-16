<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MovimientoInventarioController extends Controller
{
    public function store(Request $request, Articulo $articulo, InventarioService $inventario)
    {
        $reglas = [
            'tipo' => ['required', Rule::in(array_keys(config('inventario.tipos')))],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];

        $conDocumento = fn () => filled($request->input('tipo_documento'))
            && $request->input('tipo_documento') !== 'Sin documento';

        $porTipo = match ($request->input('tipo')) {
            'entrada' => [
                'motivo' => ['required', Rule::in(config('inventario.motivos_entrada'))],
                'origen' => ['nullable', 'string', 'max:255'],
                'valor_unitario' => ['nullable', 'integer', 'min:0'],
                'tipo_documento' => ['nullable', Rule::in(config('inventario.tipos_documento'))],
                'num_documento' => [Rule::requiredIf($conDocumento), 'nullable', 'string', 'max:50'],
            ],
            'baja' => [
                'motivo' => ['required', Rule::in(config('inventario.motivos_baja'))],
                // Toda baja queda justificada
                'observaciones' => ['required', 'string', 'min:5', 'max:500'],
            ],
            'prestamo' => [
                'prestatario' => ['required', 'string', 'max:255'],
                'prestatario_telefono' => ['nullable', 'string', 'max:30'],
                'fecha_compromiso' => ['nullable', 'date', 'after_or_equal:fecha'],
            ],
            'devolucion' => [
                'movimiento_origen_id' => ['required', 'integer'],
            ],
            default => [],
        };

        $data = $request->validate([...$reglas, ...$porTipo]);

        $inventario->registrar($articulo, $data, $request->user()->id);

        return back()->with('success', 'Movimiento registrado correctamente.');
    }

    public function anular(Request $request, MovimientoInventario $movimiento, InventarioService $inventario)
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $inventario->anular($movimiento, $data['motivo'], $request->user()->id);

        return back()->with('success', 'Movimiento anulado.');
    }
}
