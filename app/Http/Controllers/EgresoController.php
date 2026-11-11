<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\ConfiguracionCaja;
use App\Models\Egreso;
use App\Rules\MesAbierto;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EgresoController extends Controller
{
    public function store(Request $request, CajaService $caja)
    {
        $data = $request->validate([
            'fecha_egreso' => ['required', 'date', 'before_or_equal:today', new MesAbierto],
            'monto_egreso' => ['required', 'integer', 'min:1'],
            'categoria' => ['required', Rule::in(config('caja.categorias_egreso'))],
            'descripcion_egreso' => ['required', 'string', 'max:255'],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'tipo_documento' => ['required', Rule::in(config('caja.tipos_documento'))],
            'num_documento' => [
                Rule::requiredIf($request->input('tipo_documento') !== 'Sin documento'),
                'nullable',
                'string',
                'max:50',
            ],
            'medio_pago' => ['required', Rule::in(config('cuotas.medios_pago'))],
        ]);

        if (! $caja->configuracion()) {
            return back()->with('error', 'Primero define el saldo inicial de caja.');
        }

        DB::transaction(function () use ($data, $request, $caja) {
            // Se bloquea la configuración para que dos egresos simultáneos no superen el saldo
            ConfiguracionCaja::lockForUpdate()->first();

            if (! config('caja.permitir_saldo_negativo')) {
                $saldo = $caja->saldoAl(today());

                if ($data['monto_egreso'] > $saldo) {
                    throw ValidationException::withMessages([
                        'monto_egreso' => 'El egreso supera el saldo disponible ($'.number_format($saldo, 0, ',', '.').').',
                    ]);
                }
            }

            $egreso = new Egreso($data);
            $egreso->user_id = $request->user()->id; // siempre el usuario autenticado
            $egreso->save();
        });

        return back()->with('success', 'Egreso registrado correctamente.');
    }

    public function anular(Request $request, Egreso $egreso)
    {
        $data = $request->validate([
            'monto_egreso' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($egreso->anulado_at) {
            return back()->with('error', 'El egreso ya estaba anulado.');
        }

        if (CierreCaja::fechaCerrada($egreso->fecha_egreso)) {
            return back()->with('error', 'No se puede anular: el mes del egreso ya está cerrado en caja.');
        }

        $egreso->forceFill([
            'anulado_at' => now(),
            'anulado_por' => $request->user()->id,
            'motivo_anulacion' => $data['monto_egreso'],
        ])->save();

        return back()->with('success', 'Egreso anulado.');
    }
}
