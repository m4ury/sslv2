<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function __construct(private InventarioService $inventario) {}

    public function index()
    {
        $stock = $this->inventario->stockTodos();

        $articulos = Articulo::orderBy('nombre')->get()->map(function (Articulo $a) use ($stock) {
            $s = $stock[$a->id] ?? $this->inventario->calcular([]);

            return [
                'id' => $a->id,
                'codigo' => $a->codigo,
                'nombre' => $a->nombre,
                'categoria' => $a->categoria,
                'unidad' => $a->unidad,
                'ubicacion' => $a->ubicacion,
                'stock_minimo' => $a->stock_minimo,
                'activo' => $a->activo,
                'existencia' => $s['existencia'],
                'disponible' => $s['disponible'],
                'en_prestamo' => $s['en_prestamo'],
                'bajo_stock' => $a->activo && $a->stock_minimo !== null && $s['disponible'] < $a->stock_minimo,
            ];
        })->values();

        return Inertia::render('inventario/Index', [
            'articulos' => $articulos,
            'categorias' => config('inventario.categorias'),
            'resumen' => [
                'activos' => $articulos->where('activo', true)->count(),
                'con_prestamo' => $articulos->where('en_prestamo', '>', 0)->count(),
                'bajo_stock' => $articulos->where('bajo_stock', true)->count(),
                'vencidos' => MovimientoInventario::pendientes()
                    ->whereDate('fecha_compromiso', '<', today())->count(),
            ],
        ]);
    }

    public function show(Articulo $articulo)
    {
        $movimientos = $articulo->movimientos()
            ->with(['usuario:id,name', 'anulador:id,name'])
            ->orderBy('fecha')->orderBy('id')->get();

        // Saldo disponible corrido: solo cuentan los movimientos vigentes
        $disponible = 0;

        $kardex = $movimientos->map(function (MovimientoInventario $m) use (&$disponible) {
            if (! $m->anulado_at) {
                $disponible += in_array($m->tipo, ['entrada', 'devolucion'], true)
                    ? $m->cantidad
                    : -$m->cantidad;
            }

            return [...$m->toArray(), 'saldo' => $m->anulado_at ? null : $disponible];
        })->reverse()->values();

        $prestamos = $articulo->movimientos()
            ->prestamosVigentes()
            ->withSum(['devoluciones as devuelto' => fn ($q) => $q->whereNull('anulado_at')], 'cantidad')
            ->orderBy('fecha_compromiso')->orderBy('fecha')->get()
            ->filter(fn (MovimientoInventario $m) => $m->pendiente() > 0)
            ->map(fn (MovimientoInventario $m) => [
                'id' => $m->id,
                'prestatario' => $m->prestatario,
                'telefono' => $m->prestatario_telefono,
                'cantidad' => $m->cantidad,
                'devuelto' => (int) $m->devuelto,
                'pendiente' => $m->pendiente(),
                'fecha' => $m->fecha->toDateString(),
                'fecha_compromiso' => $m->fecha_compromiso?->toDateString(),
                'estado' => $m->estadoPrestamo(),
            ])->values();

        return Inertia::render('inventario/Show', [
            'articulo' => [...$articulo->toArray(), ...$this->inventario->stock($articulo->id)],
            'kardex' => $kardex,
            'prestamos' => $prestamos,
            'opciones' => $this->inventario->opciones(),
            'categorias' => config('inventario.categorias'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['nullable', 'string', 'max:30', Rule::unique('articulos', 'codigo')],
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['required', Rule::in(config('inventario.categorias'))],
            'unidad' => ['required', 'string', 'max:30'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'cantidad_inicial' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $articulo = Articulo::create(Arr::except($data, ['cantidad_inicial']));

            // Sin código manual, se asigna uno correlativo
            if (blank($articulo->codigo)) {
                $articulo->update(['codigo' => 'INV-'.str_pad($articulo->id, 4, '0', STR_PAD_LEFT)]);
            }

            // Lo que ya existía físicamente entra como inventario inicial
            if (! empty($data['cantidad_inicial'])) {
                $entrada = new MovimientoInventario([
                    'tipo' => 'entrada',
                    'motivo' => 'Inventario inicial',
                    'cantidad' => $data['cantidad_inicial'],
                    'fecha' => today()->toDateString(),
                ]);
                $entrada->articulo_id = $articulo->id;
                $entrada->user_id = $request->user()->id;
                $entrada->save();
            }
        });

        return back()->with('success', 'Artículo creado correctamente.');
    }

    public function update(Request $request, Articulo $articulo)
    {
        $data = $request->validate([
            'codigo' => ['nullable', 'string', 'max:30', Rule::unique('articulos', 'codigo')->ignore($articulo)],
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['required', Rule::in(config('inventario.categorias'))],
            'unidad' => ['required', 'string', 'max:30'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['required', 'boolean'],
        ]);

        // Un código vacío no borra el que ya tiene
        if (blank($data['codigo'] ?? null)) {
            unset($data['codigo']);
        }

        $articulo->update($data);

        return back()->with('success', 'Artículo actualizado.');
    }

    public function destroy(Articulo $articulo)
    {
        if ($articulo->movimientos()->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene movimientos. Márcalo como inactivo.');
        }

        $articulo->delete();

        return redirect('/inventario')->with('success', 'Artículo eliminado.');
    }
}
