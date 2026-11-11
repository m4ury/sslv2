<?php

namespace App\Services;

use App\Models\Aporte;
use App\Models\ConfiguracionCaja;
use App\Models\Egreso;
use App\Models\Pago;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class CajaService
{
    public function configuracion(): ?ConfiguracionCaja
    {
        return ConfiguracionCaja::first();
    }

    /** Totales vigentes entre dos fechas (inclusive) */
    public function totales(CarbonInterface $desde, CarbonInterface $hasta): array
    {
        $rango = [$desde->toDateString(), $hasta->toDateString()];

        return [
            'cuotas' => (int) Pago::whereNull('anulado_at')->whereBetween('fecha_pago', $rango)->sum('monto_pagado'),
            'aportes' => (int) Aporte::whereNull('anulado_at')->whereBetween('fecha_aporte', $rango)->sum('monto'),
            'egresos' => (int) Egreso::whereNull('anulado_at')->whereBetween('fecha_egreso', $rango)->sum('monto_egreso'),
        ];
    }

    /** Saldo al cierre del día indicado */
    public function saldoAl(CarbonInterface $fecha): int
    {
        $conf = $this->configuracion();

        if (! $conf) {
            return 0;
        }

        if ($fecha->lt($conf->fecha_inicio)) {
            return $conf->saldo_inicial;
        }

        $t = $this->totales($conf->fecha_inicio, $fecha);

        return $conf->saldo_inicial + $t['cuotas'] + $t['aportes'] - $t['egresos'];
    }

    /** Meses con estado disponible: desde el inicio de la caja hasta el mes actual */
    public function periodosDisponibles(): array
    {
        $conf = $this->configuracion();

        if (! $conf) {
            return [];
        }

        $inicio = $conf->fecha_inicio->copy()->startOfMonth();
        $periodos = [];

        for ($mes = now()->startOfMonth(); $mes->gte($inicio); $mes = $mes->subMonth()) {
            $periodos[] = $mes->format('Y-m');
        }

        return $periodos;
    }

    /** Estado de cuentas de un mes (periodo = AAAA-MM) */
    public function resumenMes(string $periodo): array
    {
        $conf = $this->configuracion();
        $inicioMes = Carbon::parse($periodo.'-01')->startOfDay();
        $finMes = $inicioMes->copy()->endOfMonth()->startOfDay();

        // Si la caja empezó a mitad de mes, se cuenta desde ese día
        $desde = ($conf && $conf->fecha_inicio->gt($inicioMes))
            ? $conf->fecha_inicio->copy()
            : $inicioMes->copy();

        $rango = [$desde->toDateString(), $finMes->toDateString()];

        $saldoInicial = match (true) {
            ! $conf => 0,
            $conf->fecha_inicio->gte($inicioMes) => $conf->saldo_inicial,
            default => $this->saldoAl($inicioMes->copy()->subDay()),
        };

        // Se arman arreglos simples: así no se disparan accesores de otros modelos
        $cuotas = Pago::with('cuota.socio.persona')
            ->whereNull('anulado_at')->whereBetween('fecha_pago', $rango)
            ->orderBy('fecha_pago')->orderBy('id')->get()
            ->map(fn (Pago $p) => [
                'id' => $p->id,
                'fecha_pago' => $p->fecha_pago->toDateString(),
                'socio' => $p->cuota?->socio?->persona?->full_name,
                'rut' => $p->cuota?->socio?->persona?->rut,
                'periodo' => $p->cuota?->periodo,
                'medio_pago' => $p->medio_pago,
                'numero_comprobante' => $p->numero_comprobante,
                'monto_cuotas' => $p->monto_pagado,
            ])->values()->all();

        $aportes = Aporte::with('colaborador.persona')
            ->whereNull('anulado_at')->whereBetween('fecha_aporte', $rango)
            ->orderBy('fecha_aporte')->orderBy('id')->get()
            ->map(fn (Aporte $a) => [
                'id' => $a->id,
                'fecha_aporte' => $a->fecha_aporte->toDateString(),
                'colaborador' => $a->colaborador?->persona?->full_name,
                'rut' => $a->colaborador?->persona?->rut,
                'medio_pago' => $a->medio_pago,
                'numero_comprobante' => $a->numero_comprobante,
                'monto_aportes' => $a->monto,
            ])->values()->all();

        $egresos = Egreso::whereNull('anulado_at')->whereBetween('fecha_egreso', $rango)
            ->orderBy('fecha_egreso')->orderBy('id')->get()
            ->map(fn (Egreso $e) => [
                'id' => $e->id,
                'fecha_egreso' => $e->fecha_egreso->toDateString(),
                'categoria' => $e->categoria,
                'descripcion_egreso' => $e->descripcion_egreso,
                'proveedor' => $e->proveedor,
                'documento' => trim($e->tipo_documento.' '.$e->num_documento),
                'medio_pago' => $e->medio_pago,
                'monto_egresos' => $e->monto_egreso,
            ])->values()->all();

        $totalCuotas = (int) collect($cuotas)->sum('monto_cuotas');
        $totalAportes = (int) collect($aportes)->sum('monto_aportes');
        $totalEgresos = (int) collect($egresos)->sum('monto_egresos');

        return [
            'periodo' => $periodo,
            'etiqueta' => ucfirst($inicioMes->copy()->locale('es')->translatedFormat('F Y')),
            'saldo_inicial' => $saldoInicial,
            'total_cuotas' => $totalCuotas,
            'total_aportes' => $totalAportes,
            'total_ingresos' => $totalCuotas + $totalAportes,
            'total_egresos' => $totalEgresos,
            'saldo_final' => $saldoInicial + $totalCuotas + $totalAportes - $totalEgresos,
            'cuotas' => $cuotas,
            'aportes' => $aportes,
            'egresos' => $egresos,
            'egresos_por_categoria' => collect($egresos)
                ->groupBy('categoria')
                ->map(fn ($g, $cat) => [
                    'categoria' => $cat,
                    'cantidad' => $g->count(),
                    'total' => $g->sum('monto'),
                ])
                ->sortByDesc('total')->values()->all(),
        ];
    }
}
