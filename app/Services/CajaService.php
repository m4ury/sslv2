<?php

namespace App\Services;

use App\Models\ConfiguracionCaja;
use App\Models\Egreso;
use App\Models\IngresoVario;
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
            'otros' => (int) IngresoVario::whereNull('anulado_at')->whereBetween('fecha', $rango)->sum('monto'),
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

        return $conf->saldo_inicial + $t['cuotas'] + $t['otros'] - $t['egresos'];
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

        $otros = IngresoVario::whereNull('anulado_at')->whereBetween('fecha', $rango)
            ->orderBy('fecha')->orderBy('id')->get()
            ->map(fn (IngresoVario $i) => [
                'id' => $i->id,
                'fecha' => $i->fecha->toDateString(),
                'categoria' => $i->categoria,
                'descripcion' => $i->descripcion,
                'origen' => $i->origen,
                'documento' => trim($i->tipo_documento.' '.$i->num_documento),
                'medio' => $i->medio_pago,
                'monto' => $i->monto,
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
        $totalOtros = (int) collect($otros)->sum('monto');
        $totalEgresos = (int) collect($egresos)->sum('monto_egresos');

        return [
            'periodo' => $periodo,
            'etiqueta' => ucfirst($inicioMes->copy()->locale('es')->translatedFormat('F Y')),
            'saldo_inicial' => $saldoInicial,
            'total_cuotas' => $totalCuotas,
            'total_otros' => $totalOtros,
            'total_ingresos' => $totalCuotas + $totalOtros,
            'total_egresos' => $totalEgresos,
            'saldo_final' => $saldoInicial + $totalCuotas + $totalOtros - $totalEgresos,
            'cuotas' => $cuotas,
            'otros' => $otros,
            'egresos' => $egresos,
            'otros_por_categoria' => $this->porCategoria($otros),
            'egresos_por_categoria' => $this->porCategoria($egresos),
                
        ];
    }
    private function porCategoria(array $items): array
    {
        return collect($items)
            ->groupBy('categoria')
            ->map(fn ($g, $cat) => [
                'categoria' => $cat,
                'cantidad' => $g->count(),
                'total' => $g->sum('monto'),
            ])
            ->sortByDesc('total')->values()->all();
    }
}
