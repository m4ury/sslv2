<?php

namespace App\Http\Controllers;

use App\Models\IngresoBancario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class IngresoBancarioController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = IngresoBancario::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('descripcion', 'like', "%{$search}%")
                        ->orWhere('banco', 'like', "%{$search}%")
                        ->orWhere('num_operacion', 'like', "%{$search}%");
                });
            })
            // El filtro se llama "categoria" para reutilizar la barra de filtros
            ->when($request->filled('categoria'), fn ($q) => $q->where('concepto', $request->query('categoria')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->query('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->query('hasta')))
            ->when($request->filled('estado'), function ($q) use ($request) {
                $request->query('estado') === 'Anulado'
                    ? $q->whereNotNull('anulado_at')
                    : $q->whereNull('anulado_at');
            });

        return Inertia::render('banco/Ingresos', [
            'ingresos' => (clone $query)
                ->with(['usuario:id,name', 'anulador:id,name'])
                ->orderByDesc('fecha')->orderByDesc('id')
                ->paginate(15)->withQueryString(),
            'total' => (int) (clone $query)->whereNull('anulado_at')->sum('monto'),
            'filters' => $request->only('search', 'categoria', 'estado', 'desde', 'hasta'),
            'conceptos' => config('banco.conceptos'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'monto' => ['required', 'integer', 'min:1'],
            'concepto' => ['required', Rule::in(config('banco.conceptos'))],
            'descripcion' => ['required', 'string', 'max:255'],
            'banco' => ['nullable', 'string', 'max:100'],
            'num_operacion' => ['nullable', 'string', 'max:50'],
        ]);

        $ingreso = new IngresoBancario($data);
        $ingreso->user_id = $request->user()->id;
        $ingreso->save();

        return back()->with('success', 'Ingreso bancario registrado correctamente.');
    }

    public function anular(Request $request, IngresoBancario $ingreso)
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($ingreso->anulado_at) {
            return back()->with('error', 'El ingreso ya estaba anulado.');
        }

        $ingreso->forceFill([
            'anulado_at' => now(),
            'anulado_por' => $request->user()->id,
            'motivo_anulacion' => $data['motivo'],
        ])->save();

        return back()->with('success', 'Ingreso bancario anulado.');
    }
}