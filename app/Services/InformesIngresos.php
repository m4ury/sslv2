<?php

namespace App\Services;

use App\Models\Aporte;
use App\Models\IngresoBancario;
use Carbon\Carbon;

class InformesIngresos
{
    public function aportes(string $periodo): array
    {
        [$desde, $hasta] = $this->rango($periodo);

        $filas = Aporte::with('colaborador.persona')
            ->whereNull('anulado_at')->whereBetween('fecha_aporte', [$desde, $hasta])
            ->orderBy('fecha_aporte')->orderBy('id')->get()
            ->map(fn (Aporte $a) => [
                'fecha' => $a->fecha_aporte->toDateString(),
                'colaborador' => $a->colaborador?->persona?->full_name,
                'rut' => $a->colaborador?->persona?->rut,
                'medio' => $a->medio_pago,
                'comprobante' => $a->numero_comprobante,
                'monto' => $a->monto,
            ])->values()->all();

        return $this->armar(
            'Informe de aportes de colaboradores',
            $periodo,
            $filas,
            [
                ['key' => 'fecha', 'label' => 'Fecha', 'tipo' => 'fecha'],
                ['key' => 'colaborador', 'label' => 'Colaborador'],
                ['key' => 'rut', 'label' => 'RUT'],
                ['key' => 'medio', 'label' => 'Medio'],
                ['key' => 'comprobante', 'label' => 'Comprobante'],
                ['key' => 'monto', 'label' => 'Monto', 'tipo' => 'dinero'],
            ],
            [
                ['titulo' => 'Por colaborador', 'clave' => 'colaborador'],
                ['titulo' => 'Por medio de pago', 'clave' => 'medio'],
            ],
            $this->periodos(Aporte::class, 'fecha_aporte'),
        );
    }

    public function banco(string $periodo): array
    {
        [$desde, $hasta] = $this->rango($periodo);

        $filas = IngresoBancario::whereNull('anulado_at')->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')->orderBy('id')->get()
            ->map(fn (IngresoBancario $i) => [
                'fecha' => $i->fecha->toDateString(),
                'concepto' => $i->concepto,
                'descripcion' => $i->descripcion,
                'banco' => $i->banco,
                'operacion' => $i->num_operacion,
                'monto' => $i->monto,
            ])->values()->all();

        return $this->armar(
            'Informe de ingresos bancarios',
            $periodo,
            $filas,
            [
                ['key' => 'fecha', 'label' => 'Fecha', 'tipo' => 'fecha'],
                ['key' => 'concepto', 'label' => 'Concepto'],
                ['key' => 'descripcion', 'label' => 'Descripción'],
                ['key' => 'banco', 'label' => 'Banco'],
                ['key' => 'operacion', 'label' => 'N° operación'],
                ['key' => 'monto', 'label' => 'Monto', 'tipo' => 'dinero'],
            ],
            [
                ['titulo' => 'Por concepto', 'clave' => 'concepto'],
                ['titulo' => 'Por banco', 'clave' => 'banco'],
            ],
            $this->periodos(IngresoBancario::class, 'fecha'),
        );
    }

    private function armar(string $titulo, string $periodo, array $filas, array $columnas, array $agrupaciones, array $periodos): array
    {
        return [
            'titulo' => $titulo,
            'periodo' => $periodo,
            'etiqueta' => ucfirst(Carbon::parse($periodo.'-01')->locale('es')->translatedFormat('F Y')),
            'periodos' => $periodos,
            'columnas' => $columnas,
            'filas' => $filas,
            'cantidad' => count($filas),
            'total' => (int) collect($filas)->sum('monto'),
            'agrupaciones' => collect($agrupaciones)->map(fn ($a) => [
                'titulo' => $a['titulo'],
                'filas' => collect($filas)
                    ->groupBy(fn ($f) => $f[$a['clave']] ?: 'Sin dato')
                    ->map(fn ($g, $nombre) => [
                        'nombre' => $nombre,
                        'cantidad' => $g->count(),
                        'total' => $g->sum('monto'),
                    ])
                    ->sortByDesc('total')->values()->all(),
            ])->all(),
        ];
    }

    private function rango(string $periodo): array
    {
        $inicio = Carbon::parse($periodo.'-01');

        return [$inicio->toDateString(), $inicio->copy()->endOfMonth()->toDateString()];
    }

    /** Meses desde el primer movimiento hasta el mes actual */
    private function periodos(string $modelo, string $columna): array
    {
        $primera = $modelo::whereNull('anulado_at')->min($columna);
        $inicio = $primera ? Carbon::parse($primera)->startOfMonth() : now()->startOfMonth();
        $periodos = [];

        for ($mes = now()->startOfMonth(); $mes->gte($inicio); $mes->subMonth()) {
            $periodos[] = $mes->format('Y-m');
        }

        return $periodos;
    }
}