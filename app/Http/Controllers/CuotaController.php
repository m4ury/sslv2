<?php

namespace App\Http\Controllers;

use App\Models\ConfigCuota;
use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CuotaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $rut = str_replace(['.', ' '], '', $search);

        $cuotas = Cuota::query()
            ->with([
                'socio.persona',
                'configuracion:id,nombre_cuota',
                'pagos' => fn ($q) => $q
                    ->with(['usuario:id,name', 'anulador:id,name'])
                    ->orderByDesc('fecha_pago')
                    ->orderByDesc('id'),
            ])
            ->when($search !== '', function ($q) use ($search, $rut) {
                $q->whereHas('socio.persona', function ($p) use ($search, $rut) {
                    $p->where('rut', 'like', "%{$rut}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellido_paterno', 'like', "%{$search}%")
                        ->orWhere('apellido_materno', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('periodo'), fn ($q) => $q->where('periodo', $request->query('periodo')))
            ->when($request->filled('config'), fn ($q) => $q->where('config_cuota_id', $request->query('config')))
            ->when($request->filled('estado'), function ($q) use ($request) {
                $estado = $request->query('estado');

                match ($estado) {
                    'Vencida' => $q->whereIn('estado', [Cuota::PENDIENTE, Cuota::PARCIAL])
                        ->whereDate('fecha_vencimiento', '<', today()),
                    'Pendiente', 'Parcial' => $q->where('estado', $estado)
                        ->whereDate('fecha_vencimiento', '>=', today()),
                    default => $q->where('estado', $estado),
                };
            })
            ->orderByDesc('periodo')
            ->orderBy('socio_id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('cuotas/Index', [
            'cuotas' => $cuotas,
            'filters' => $request->only('search', 'estado', 'periodo', 'config'),
            'periodos' => Cuota::select('periodo')->distinct()->orderByDesc('periodo')->pluck('periodo'),
            'configuraciones' => ConfigCuota::orderBy('nombre_cuota')->get(['id', 'nombre_cuota']),
            'medios' => config('cuotas.medios_pago'),
            'resumen' => [
                'recaudado_mes' => (int) Pago::whereNull('anulado_at')
                    ->whereBetween('fecha_pago', [today()->startOfMonth(), today()->endOfMonth()])
                    ->sum('monto_pagado'),
                'vencidas' => Cuota::whereIn('estado', [Cuota::PENDIENTE, Cuota::PARCIAL])
                    ->whereDate('fecha_vencimiento', '<', today())
                    ->count(),
            ],
        ]);
    }
}
