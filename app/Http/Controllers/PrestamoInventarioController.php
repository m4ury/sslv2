<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestamoInventarioController extends Controller
{
    public function index(Request $request, InventarioService $inventario)
    {
        $estado = $request->query('estado', 'Pendientes');
        $search = trim((string) $request->query('search', ''));

        $query = match ($estado) {
            'Vencido' => MovimientoInventario::pendientes()->whereDate('fecha_compromiso', '<', today()),
            'Devuelto' => MovimientoInventario::devueltos(),
            'Todos' => MovimientoInventario::prestamosVigentes(),
            default => MovimientoInventario::pendientes(),
        };

        $prestamos = $query
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('prestatario', 'like', "%{$search}%")
                        ->orWhereHas('articulo', function ($a) use ($search) {
                            $a->where('nombre', 'like', "%{$search}%")->orWhere('codigo', 'like', "%{$search}%");
                        });
                });
            })
            ->with('articulo:id,codigo,nombre,unidad')
            ->withSum(['devoluciones as devuelto' => fn ($q) => $q->whereNull('anulado_at')], 'cantidad')
            ->orderBy('fecha_compromiso')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (MovimientoInventario $m) => [
                'id' => $m->id,
                'articulo' => $m->articulo,
                'prestatario' => $m->prestatario,
                'telefono' => $m->prestatario_telefono,
                'cantidad' => $m->cantidad,
                'devuelto' => (int) $m->devuelto,
                'pendiente' => $m->pendiente(),
                'fecha' => $m->fecha->toDateString(),
                'fecha_compromiso' => $m->fecha_compromiso?->toDateString(),
                'estado' => $m->estadoPrestamo(),
            ]);

        return Inertia::render('inventario/Prestamos', [
            'prestamos' => $prestamos,
            'filters' => ['estado' => $estado, 'search' => $search],
            'opciones' => $inventario->opciones(),
        ]);
    }
}
