<?php

namespace App\Services;

use App\Models\Aporte;
use App\Models\Articulo;
use App\Models\CierreCaja;
use App\Models\Colaborador;
use App\Models\Credencial;
use App\Models\Cuidador;
use App\Models\Cuota;
use App\Models\IngresoBancario;
use App\Models\MovimientoInventario;
use App\Models\Pago;
use App\Models\Programado;
use App\Models\Socio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private CajaService $cajaService,
        private InventarioService $inventarioService,
    ) {}

    /** Solo se calcula lo que el usuario tiene permiso de ver */
    public function para(User $user): array
    {
        $puede = fn (string $permiso) => $user->tienePermiso($permiso);

        $datos = [
            'agenda' => $puede('agenda.ver') ? $this->widgetAgenda() : null,
            'clinico' => $puede('ficha.ver') ? $this->widgetClinico() : null,
            'personas' => $puede('personas.ver') ? $this->widgetPersonas() : null,
            'cuotas' => $puede('cuotas.ver') ? $this->widgetCuotas() : null,
            'caja' => $puede('caja.ver') ? $this->widgetCaja() : null,
            'reservados' => $this->widgetReservados($puede),
            'inventario' => $puede('inventario.ver') ? $this->widgetInventario() : null,
        ];

        return [...$datos, 'alertas' => $this->alertas($datos)];
    }

    private function widgetAgenda(): array
    {
        $hoy = today();

        $lista = Programado::with('socio.persona')
            ->whereDate('fecha_programada', $hoy)
            ->where('estado', '!=', 'Cancelado')
            ->orderByRaw('hora IS NULL, hora')
            ->get()
            ->map(fn (Programado $p) => [
                'id' => $p->id,
                'socio_id' => $p->socio_id,
                'socio' => $p->socio?->persona?->full_name,
                'hora' => $p->hora,
                'fin' => $p->hora ? Carbon::parse($p->hora)->addMinutes($p->duracion_min)->format('H:i') : null,
                'tipo' => $p->tipo,
                'estado' => $p->estado,
            ])->values();

        return [
            'hoy' => $lista->count(),
            'proximos' => Programado::where('estado', 'Pendiente')
                ->whereDate('fecha_programada', '>', $hoy)
                ->whereDate('fecha_programada', '<=', $hoy->copy()->addDays(7))
                ->count(),
            'atrasados' => Programado::where('estado', 'Pendiente')
                ->whereDate('fecha_programada', '<', $hoy)
                ->count(),
            'lista' => $lista->take(8)->values()->all(),
        ];
    }

    private function widgetClinico(): array
    {
        $hoy = today();
        $dias = (int) config('dashboard.dias_credencial', 30);
        $diasSinControl = (int) config('dashboard.dias_sin_control', 90);

        $credenciales = Credencial::with('socio.persona')
            ->whereNotNull('fecha_expiracion')
            ->whereDate('fecha_expiracion', '<=', $hoy->copy()->addDays($dias))
            ->whereHas('socio', fn ($q) => $q->where('estado', 'Activo'));

        // Socio activos sin control reciente y sin control programado a futuro
        $sinControl = Socio::with('persona')
            ->where('estado', 'Activo')
            ->whereDoesntHave('controles', fn ($q) => $q->whereDate('fecha_control', '>=', $hoy->copy()->subDays($diasSinControl)))
            ->whereDoesntHave('programados', fn ($q) => $q
                ->where('estado', 'Pendiente')
                ->whereDate('fecha_programada', '>=', $hoy));

        return [
            'credenciales_total' => (clone $credenciales)->count(),
            'credenciales_vencidas' => (clone $credenciales)->whereDate('fecha_expiracion', '<', $hoy)->count(),
            'credenciales' => (clone $credenciales)->orderBy('fecha_expiracion')->limit(5)->get()
                ->map(fn (Credencial $c) => [
                    'id' => $c->id,
                    'socio_id' => $c->socio_id,
                    'socio' => $c->socio?->persona?->full_name,
                    'tipo' => $c->tipo,
                    'vencimiento' => $c->fecha_expiracion->toDateString(),
                    'vencida' => $c->fecha_expiracion->lt($hoy),
                ])->values()->all(),
            'dias_sin_control' => $diasSinControl,
            'sin_control' => (clone $sinControl)->count(),
            'sin_control_lista' => (clone $sinControl)->orderBy('id')->limit(5)->get()
                ->map(fn (Socio $a) => [
                    'socio_id' => $a->id,
                    'socio' => $a->persona?->full_name,
                ])->values()->all(),
        ];
    }

    private function widgetPersonas(): array
    {
        return [
            'socios' => Socio::where('estado', 'Activo')->count(),
            'colaboradores' => Colaborador::where('estado', 'Activo')->count(),
            'cuidadores' => Cuidador::where('estado', 'Activo')->count(),
            'socios_sin_cuidador' => Socio::where('estado', 'Activo')
                ->whereDoesntHave('cuidadores', fn ($q) => $q->where('es_principal', true)->whereNull('fecha_fin'))
                ->count(),
        ];
    }

    private function widgetCuotas(): array
    {
        $hoy = today();
        $periodo = $hoy->format('Y-m');

        $tCuotas = (new Cuota)->getTable();
        $tPagos = (new Pago)->getTable();

        $emitido = (int) Cuota::where('periodo', $periodo)->sum('monto_cuota');

        $cobrado = (int) Pago::whereNull('anulado_at')
            ->whereHas('cuota', fn ($q) => $q->where('periodo', $periodo))
            ->sum('monto_pagado');

        // Saldo de cada cuota vencida: monto menos los pagos vigentes
        $vencidas = DB::table($tCuotas)
            ->leftJoin($tPagos, function ($join) use ($tCuotas, $tPagos) {
                $join->on("{$tPagos}.cuota_id", '=', "{$tCuotas}.id")->whereNull("{$tPagos}.anulado_at");
            })
            ->whereIn("{$tCuotas}.estado", [Cuota::PENDIENTE, Cuota::PARCIAL])
            ->whereDate("{$tCuotas}.fecha_vencimiento", '<', $hoy)
            ->groupBy("{$tCuotas}.id", "{$tCuotas}.socio_id", "{$tCuotas}.monto_cuota")
            ->selectRaw("{$tCuotas}.socio_id, {$tCuotas}.monto_cuota - COALESCE(SUM({$tPagos}.monto_pagado), 0) AS saldo")
            ->get();

        return [
            'periodo_etiqueta' => ucfirst($hoy->copy()->locale('es')->translatedFormat('F Y')),
            'emitido' => $emitido,
            'cobrado' => $cobrado,
            'porcentaje' => $emitido > 0 ? (int) round($cobrado / $emitido * 100) : null,
            'vencidas' => $vencidas->count(),
            'adeudado' => (int) $vencidas->sum('saldo'),
            'morosos' => $vencidas->pluck('socio_id')->unique()->count(),
        ];
    }

    private function widgetCaja(): array
    {
        $conf = $this->cajaService->configuracion();

        if (! $conf) {
            return ['configurada' => false];
        }

        $hoy = today();
        $mes = $this->cajaService->totales($hoy->copy()->startOfMonth()->max($conf->fecha_inicio), $hoy);

        // ¿El mes anterior ya terminó y sigue sin cerrar?
        $anterior = $hoy->copy()->startOfMonth()->subMonth();
        $pendiente = null;

        if ($anterior->gte($conf->fecha_inicio->copy()->startOfMonth())
            && ! CierreCaja::where('periodo', $anterior->format('Y-m'))->exists()) {
            $pendiente = [
                'periodo' => $anterior->format('Y-m'),
                'etiqueta' => ucfirst($anterior->copy()->locale('es')->translatedFormat('F Y')),
            ];
        }

        return [
            'configurada' => true,
            'saldo' => $this->cajaService->saldoAl($hoy),
            'ingresos_mes' => $mes['cuotas'] + $mes['otros'],
            'egresos_mes' => $mes['egresos'],
            'mes_pendiente_cierre' => $pendiente,
            'serie' => $this->serieMensual((int) config('dashboard.meses_grafico', 6)),
        ];
    }

    /** Ingresos (cuotas + otros) y egresos de los últimos N meses, con 3 consultas */
    private function serieMensual(int $meses): array
    {
        $inicio = today()->startOfMonth()->subMonths($meses - 1);
        $rango = [$inicio->toDateString(), today()->endOfMonth()->toDateString()];

        $sumar = fn (string $tabla, string $fecha, string $monto) => DB::table($tabla)
            ->whereNull('anulado_at')
            ->whereBetween($fecha, $rango)
            ->selectRaw("DATE_FORMAT({$fecha}, '%Y-%m') AS periodo, SUM({$monto}) AS total")
            ->groupBy('periodo')
            ->pluck('total', 'periodo');

        $cuotas = $sumar((new Pago)->getTable(), 'fecha_pago', 'monto_pagado');
        $otros = $sumar('ingresos_varios', 'fecha', 'monto');
        $egresos = $sumar('egresos', 'fecha_egreso', 'monto_egreso');

        $serie = [];

        for ($mes = $inicio->copy(); $mes->lte(today()); $mes = $mes->addMonth()) {
            $p = $mes->format('Y-m');

            $serie[] = [
                'periodo' => $p,
                'etiqueta' => ucfirst(rtrim($mes->copy()->locale('es')->translatedFormat('M'), '.')),
                'ingresos' => (int) ($cuotas[$p] ?? 0) + (int) ($otros[$p] ?? 0),
                'egresos' => (int) ($egresos[$p] ?? 0),
            ];
        }

        return $serie;
    }

    private function widgetReservados(callable $puede): ?array
    {
        $aportes = $puede('aportes.informe') ? $this->totalMes(Aporte::query(), 'fecha_aporte') : null;
        $banco = $puede('banco.ver') ? $this->totalMes(IngresoBancario::query(), 'fecha') : null;

        return ($aportes || $banco) ? compact('aportes', 'banco') : null;
    }

    private function totalMes($query, string $fecha): array
    {
        $q = $query->whereNull('anulado_at')
            ->whereBetween($fecha, [today()->startOfMonth()->toDateString(), today()->toDateString()]);

        return [
            'total' => (int) (clone $q)->sum('monto'),
            'cantidad' => (clone $q)->count(),
        ];
    }

    private function widgetInventario(): array
    {
        $hoy = today();
        $stock = $this->inventarioService->stockTodos();

        $bajo = Articulo::where('activo', true)
            ->whereNotNull('stock_minimo')
            ->orderBy('nombre')->get()
            ->filter(fn (Articulo $a) => ($stock[$a->id]['disponible'] ?? 0) < $a->stock_minimo)
            ->map(fn (Articulo $a) => [
                'id' => $a->id,
                'nombre' => $a->nombre,
                'disponible' => $stock[$a->id]['disponible'] ?? 0,
                'minimo' => $a->stock_minimo,
            ])->values();

        $vencidos = MovimientoInventario::pendientes()
            ->whereDate('fecha_compromiso', '<', $hoy)
            ->with('articulo:id,nombre')
            ->withSum(['devoluciones as devuelto' => fn ($q) => $q->whereNull('anulado_at')], 'cantidad')
            ->orderBy('fecha_compromiso')
            ->get();

        return [
            'prestamos_pendientes' => MovimientoInventario::pendientes()->count(),
            'vencidos' => $vencidos->count(),
            'vencidos_lista' => $vencidos->take(5)->map(fn (MovimientoInventario $m) => [
                'id' => $m->id,
                'articulo_id' => $m->articulo_id,
                'articulo' => $m->articulo?->nombre,
                'prestatario' => $m->prestatario,
                'pendiente' => $m->pendiente(),
                'dias' => abs((int) $m->fecha_compromiso->diffInDays($hoy)),
            ])->values()->all(),
            'bajo_stock' => $bajo->count(),
            'bajo_stock_lista' => $bajo->take(5)->all(),
        ];
    }

    /** Resumen de lo urgente, usando los datos ya calculados (sin consultas extra) */
    private function alertas(array $d): array
    {
        $alertas = [];
        $fmt = fn (int $n) => '$'.number_format($n, 0, ',', '.');
        $pl = fn (int $n, string $uno, string $varios) => $n === 1 ? $uno : $varios;

        if (($d['inventario']['vencidos'] ?? 0) > 0) {
            $n = $d['inventario']['vencidos'];
            $alertas[] = [
                'nivel' => 'danger',
                'texto' => "{$n} ".$pl($n, 'préstamo vencido sin devolver', 'préstamos vencidos sin devolver'),
                'href' => '/inventario/prestamos?estado=Vencido',
            ];
        }

        if (($d['clinico']['credenciales_vencidas'] ?? 0) > 0) {
            $n = $d['clinico']['credenciales_vencidas'];
            $alertas[] = [
                'nivel' => 'danger',
                'texto' => "{$n} ".$pl($n, 'credencial vencida', 'credenciales vencidas').' de socios activos',
                'href' => null,
            ];
        }

        if (($d['agenda']['atrasados'] ?? 0) > 0) {
            $n = $d['agenda']['atrasados'];
            $alertas[] = [
                'nivel' => 'warning',
                'texto' => "{$n} ".$pl($n, 'control programado quedó sin atender', 'controles programados quedaron sin atender'),
                'href' => '/agenda',
            ];
        }

        if (($d['cuotas']['vencidas'] ?? 0) > 0) {
            $c = $d['cuotas'];
            $alertas[] = [
                'nivel' => 'warning',
                'texto' => "{$c['vencidas']} ".$pl($c['vencidas'], 'cuota vencida', 'cuotas vencidas')
                    .' por '.$fmt($c['adeudado'])." ({$c['morosos']} ".$pl($c['morosos'], 'socio', 'socios').')',
                'href' => '/cuotas?estado=Vencida',
            ];
        }

        if (! empty($d['caja']['mes_pendiente_cierre'])) {
            $m = $d['caja']['mes_pendiente_cierre'];
            $alertas[] = [
                'nivel' => 'info',
                'texto' => "El mes de {$m['etiqueta']} aún no está cerrado en caja",
                'href' => "/caja/estado/{$m['periodo']}",
            ];
        }

        if (($d['clinico']['sin_control'] ?? 0) > 0) {
            $n = $d['clinico']['sin_control'];
            $alertas[] = [
                'nivel' => 'info',
                'texto' => "{$n} ".$pl($n, 'Paciente sin control', 'socios sin control')
                    ." en {$d['clinico']['dias_sin_control']} días ni control programado",
                'href' => null,
            ];
        }

        if (($d['personas']['socios_sin_cuidador'] ?? 0) > 0) {
            $n = $d['personas']['socios_sin_cuidador'];
            $alertas[] = [
                'nivel' => 'info',
                'texto' => "{$n} ".$pl($n, 'Paciente activo sin cuidador principal', 'socios activos sin cuidador principal'),
                'href' => null,
            ];
        }

        if (($d['inventario']['bajo_stock'] ?? 0) > 0) {
            $n = $d['inventario']['bajo_stock'];
            $alertas[] = [
                'nivel' => 'info',
                'texto' => "{$n} ".$pl($n, 'artículo bajo el stock mínimo', 'artículos bajo el stock mínimo'),
                'href' => '/inventario',
            ];
        }

        return $alertas;
    }
}
