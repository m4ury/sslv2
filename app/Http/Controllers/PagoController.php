<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Pago;
use App\Services\RegistroPagos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\CierreCaja;
use App\Rules\MesAbierto;

class PagoController extends Controller
{
    public function store(Request $request, Cuota $cuota, RegistroPagos $registro)
    {
        $data = $request->validate([
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
            'monto_pagado' => ['required', 'integer', 'min:1'],
            'medio_pago' => ['required', Rule::in(config('cuotas.medios_pago'))],
            'numero_comprobante' => [
                Rule::requiredIf($request->input('medio_pago') !== 'Efectivo'),
                'nullable',
                'string',
                'max:50',
                // El mismo comprobante no se registra dos veces con el mismo medio
                Rule::unique('pagos', 'numero_comprobante')
                    ->where('medio_pago', $request->input('medio_pago'))
                    ->whereNull('anulado_at'),
                'fecha_pago' => ['required', 'date', 'before_or_equal:today', new MesAbierto],
            ],
        ]);

        $registro->registrar($cuota, $data, $request->user()->id);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function anular(Request $request, Pago $pago, RegistroPagos $registro)
    {

        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (CierreCaja::fechaCerrada($pago->fecha_pago)) {
            return back()->with('error', 'No se puede anular: el mes del pago ya está cerrado en caja.');
        }

        $registro->anular($pago, $data['motivo'], $request->user()->id);

        return back()->with('success', 'Pago anulado. La cuota fue recalculada.');
    }
}
