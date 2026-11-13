<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\IngresoVario;
use App\Rules\MesAbierto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class IngresoVarioController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = IngresoVario::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('descripcion', 'like', "%{$search}%")
                        ->orWhere('origen', 'like', "%{$search}%")
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
            });

        return Inertia::render('caja/IngresosVarios', [
            'ingresos' => (clone $query)
                ->with(['usuario:id,name', 'anulador:id,name'])
                ->orderByDesc('fecha')->orderByDesc('id')
                ->paginate(15)->withQueryString(),
            'total' => (int) (clone $query)->whereNull('anulado_at')->sum('monto'),
            'filters' => $request->only('search', 'categoria', 'estado', 'desde', 'hasta'),
            'categorias' => config('caja.categorias_ingreso'),
            'tiposDocumento' => config('caja.tipos_documento_ingreso'),
            'medios' => config('cuotas.medios_pago'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today', new MesAbierto],
            'monto' => ['required', 'integer', 'min:1'],
            'categoria' => ['required', Rule::in(config('caja.categorias_ingreso'))],
            'descripcion' => ['required', 'string', 'max:255'],
            'origen' => ['nullable', 'string', 'max:255'],
            'tipo_documento' => ['required', Rule::in(config('caja.tipos_documento_ingreso'))],
            'num_documento' => [
                Rule::requiredIf($request->input('tipo_documento') !== 'Sin documento'),
                'nullable',
                'string',
                'max:50',
            ],
            'medio_pago' => ['required', Rule::in(config('cuotas.medios_pago'))],
        ]);

        $ingreso = new IngresoVario($data);
        $ingreso->user_id = $request->user()->id; // siempre el usuario autenticado
        $ingreso->save();

        return back()->with('success', 'Ingreso registrado correctamente.');
    }

    public function anular(Request $request, IngresoVario $ingreso)
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($ingreso->anulado_at) {
            return back()->with('error', 'El ingreso ya estaba anulado.');
        }

        if (CierreCaja::fechaCerrada($ingreso->fecha)) {
            return back()->with('error', 'No se puede anular: el mes del ingreso ya está cerrado en caja.');
        }

        $ingreso->forceFill([
            'anulado_at' => now(),
            'anulado_por' => $request->user()->id,
            'motivo_anulacion' => $data['motivo'],
        ])->save();

        return back()->with('success', 'Ingreso anulado.');
    }
}