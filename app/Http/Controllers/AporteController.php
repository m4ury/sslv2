<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\Colaborador;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\CierreCaja;
use App\Rules\MesAbierto;

class AporteController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $rut = str_replace(['.', ' '], '', $search);

        $query = Aporte::query()
            ->when($search !== '', function ($q) use ($search, $rut) {
                $q->whereHas('colaborador.persona', function ($p) use ($search, $rut) {
                    $p->where('rut', 'like', "%{$rut}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellido_paterno', 'like', "%{$search}%")
                        ->orWhere('apellido_materno', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('medio'), fn ($q) => $q->where('medio_pago', $request->query('medio')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha_aporte', '>=', $request->query('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha_aporte', '<=', $request->query('hasta')))
            ->when($request->filled('estado'), function ($q) use ($request) {
                $request->query('estado') === 'Anulado'
                    ? $q->whereNotNull('anulado_at')
                    : $q->whereNull('anulado_at');
            });

        // Totales sobre lo filtrado, contando solo aportes vigentes
        $resumen = [
            'total' => (int) (clone $query)->whereNull('anulado_at')->sum('monto'),
            'cantidad' => (clone $query)->whereNull('anulado_at')->count(),
        ];

        $aportes = $query
            ->with(['colaborador.persona', 'usuario:id,name', 'anulador:id,name'])
            ->orderByDesc('fecha_aporte')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('aportes/Index', [
            'aportes' => $aportes,
            'filters' => $request->only('search', 'medio', 'desde', 'hasta', 'estado'),
            'medios' => config('cuotas.medios_pago'),
            'resumen' => $resumen,
        ]);
    }

    public function store(Request $request, Colaborador $colaborador)
    {
        $data = $request->validate([
            'fecha_aporte' => ['required', 'date', 'before_or_equal:today'],
            'monto' => ['required', 'integer', 'min:1'],
            'medio_pago' => ['required', Rule::in(config('cuotas.medios_pago'))],
            'numero_comprobante' => [
                Rule::requiredIf($request->input('medio_pago') !== 'Efectivo'),
                'nullable',
                'string',
                'max:50',
                // El mismo comprobante no se registra dos veces con el mismo medio
                Rule::unique('aportes', 'numero_comprobante')
                    ->where('medio_pago', $request->input('medio_pago'))
                    ->whereNull('anulado_at'),
                'fecha_aporte' => ['required', 'date', 'before_or_equal:today', new MesAbierto],
            ],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $aporte = new Aporte($data);
        $aporte->colaborador_id = $colaborador->id;
        $aporte->user_id = $request->user()->id; // siempre el usuario autenticado
        $aporte->save();

        return back()->with('success', 'Aporte registrado correctamente.');
    }

    public function anular(Request $request, Aporte $aporte)
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($aporte->anulado_at) {
            return back()->with('error', 'El aporte ya estaba anulado.');
        }

        if (CierreCaja::fechaCerrada($aporte->fecha_aporte)) {
            return back()->with('error', 'No se puede anular: el mes del aporte ya está cerrado en caja.');
        }

        $aporte->forceFill([
            'anulado_at' => now(),
            'anulado_por' => $request->user()->id,
            'motivo_anulacion' => $data['motivo'],
        ])->save();

        return back()->with('success', 'Aporte anulado.');
    }
}
