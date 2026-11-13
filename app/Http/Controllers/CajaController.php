<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\ConfiguracionCaja;
use App\Models\Egreso;
use App\Services\CajaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CajaController extends Controller
{
    public function __construct(private CajaService $caja) {}

    public function index(Request $request)
    {
        $conf = $this->caja->configuracion();
        $search = trim((string) $request->query('search', ''));
        $hoy = today();

        $egresos = Egreso::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('descripcion_egreso', 'like', "%{$search}%")
                        ->orWhere('proveedor', 'like', "%{$search}%")
                        ->orWhere('num_documento', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->query('categoria')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->query('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->query('hasta')))
            ->when($request->filled('estado'), function ($q) use ($request) {
                $request->query('estado') === 'Anulado'
                    ? $q->whereNotNull('anulado_at')
                    : $q->whereNull('anulado_at');
            })
            ->with(['usuario:id,name', 'anulador:id,name'])
            ->orderByDesc('fecha_egreso')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $mes = ['cuotas' => 0, 'aportes' => 0, 'egresos' => 0];

        if ($conf) {
            $mes = $this->caja->totales($hoy->copy()->startOfMonth()->max($conf->fecha_inicio), $hoy);
        }

        return Inertia::render('caja/Index', [
            'configuracion' => $conf,
            'configEditable' => ! CierreCaja::exists(),
            'saldo' => $this->caja->saldoAl($hoy),
            'mes' => $mes,
            'egresos' => $egresos,
            'filters' => $request->only('search', 'categoria', 'estado', 'desde', 'hasta'),
            'categorias' => config('caja.categorias_egreso'),
            'tiposDocumento' => config('caja.tipos_documento'),
            'medios' => config('cuotas.medios_pago'),
        ]);
    }

    public function estado(?string $periodo = null)
    {
        $periodo ??= now()->format('Y-m');

        return Inertia::render('caja/Estado', [
            ...$this->datosEstado($periodo),
            'periodo' => $periodo,
            'periodos' => $this->caja->periodosDisponibles(),
            'bloqueoCierre' => $this->razonNoCerrable($periodo),
            'esUltimoCierre' => CierreCaja::orderByDesc('periodo')->value('periodo') === $periodo,
        ]);
    }

    public function pdf(Request $request, string $periodo)
    {
        abort_unless($this->caja->configuracion(), 404);

        return Pdf::loadView('caja.estado', [
            ...$this->datosEstado($periodo),
            'agrupacion' => config('app.name'),
            'generadoPor' => $request->user()->name,
            'generadoEn' => now(),
        ])->setPaper('a4')->download("estado-de-cuentas-{$periodo}.pdf");
    }

    public function cerrar(Request $request, string $periodo)
    {
        if ($razon = $this->razonNoCerrable($periodo)) {
            return back()->with('error', $razon);
        }

        $r = $this->caja->resumenMes($periodo);

        $cierre = new CierreCaja([
            'periodo' => $periodo,
            'saldo_inicial' => $r['saldo_inicial'],
            'total_cuotas' => $r['total_cuotas'],
            'total_otros_ingresos' => $r['total_otros'],
            'total_egresos' => $r['total_egresos'],
            'saldo_final' => $r['saldo_final'],
        ]);
        $cierre->cerrado_por = $request->user()->id;
        $cierre->cerrado_at = now();
        $cierre->save();

        return back()->with('success', 'Mes cerrado. El estado de cuentas quedó definitivo.');
    }

    public function reabrir(string $periodo)
    {
        $ultimo = CierreCaja::orderByDesc('periodo')->first();

        if (! $ultimo || $ultimo->periodo !== $periodo) {
            return back()->with('error', 'Solo se puede reabrir el último mes cerrado.');
        }

        $ultimo->delete();

        return back()->with('success', 'Mes reabierto.');
    }

    public function configurar(Request $request)
    {
        if (CierreCaja::exists()) {
            return back()->with('error', 'No se puede modificar el saldo inicial: ya hay meses cerrados.');
        }

        $data = $request->validate([
            'fecha_inicio' => ['required', 'date'],
            'saldo_inicial' => ['required', 'integer', 'min:0'],
        ]);

        $conf = ConfiguracionCaja::first() ?? new ConfiguracionCaja;
        $conf->fill($data);
        $conf->actualizado_por = $request->user()->id;
        $conf->save();

        return back()->with('success', 'Saldo inicial de caja guardado.');
    }

    private function datosEstado(string $periodo): array
    {
        return [
            'configuracion' => $this->caja->configuracion(),
            'resumen' => $this->caja->resumenMes($periodo),
            'cierre' => CierreCaja::with('cerrador:id,name')->where('periodo', $periodo)->first(),
        ];
    }

    // null si el mes se puede cerrar; si no, el motivo
    private function razonNoCerrable(string $periodo): ?string
    {
        $conf = $this->caja->configuracion();

        if (! $conf) {
            return 'Define primero el saldo inicial de caja.';
        }

        if (CierreCaja::where('periodo', $periodo)->exists()) {
            return 'El mes ya está cerrado.';
        }

        $inicio = Carbon::parse($periodo.'-01')->startOfDay();
        $primerMes = $conf->fecha_inicio->copy()->startOfMonth();

        if ($inicio->lt($primerMes)) {
            return 'El mes es anterior al inicio de la caja.';
        }

        if ($inicio->copy()->endOfMonth()->gte(today())) {
            return 'Solo se puede cerrar un mes que ya terminó.';
        }

        $anterior = $inicio->copy()->subMonth();

        if ($inicio->gt($primerMes) && ! CierreCaja::where('periodo', $anterior->format('Y-m'))->exists()) {
            return 'Primero debes cerrar '.$anterior->format('m/Y').'.';
        }

        return null;
    }
}
