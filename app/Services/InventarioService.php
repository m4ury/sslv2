<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioService
{
    /** @param array<string, int|string> $t tipo => cantidad total */
    public function calcular(array $t): array
    {
        $entradas = (int) ($t['entrada'] ?? 0);
        $bajas = (int) ($t['baja'] ?? 0);
        $prestado = (int) ($t['prestamo'] ?? 0);
        $devuelto = (int) ($t['devolucion'] ?? 0);

        $existencia = $entradas - $bajas;
        $enPrestamo = $prestado - $devuelto;

        return [
            'existencia' => $existencia,
            'en_prestamo' => $enPrestamo,
            'disponible' => $existencia - $enPrestamo,
        ];
    }

    public function stock(int $articuloId): array
    {
        $totales = MovimientoInventario::where('articulo_id', $articuloId)
            ->whereNull('anulado_at')
            ->selectRaw('tipo, SUM(cantidad) AS total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->all();

        return $this->calcular($totales);
    }

    /** Stock de todos los artículos: [articulo_id => stock] */
    public function stockTodos(): array
    {
        return MovimientoInventario::whereNull('anulado_at')
            ->selectRaw('articulo_id, tipo, SUM(cantidad) AS total')
            ->groupBy('articulo_id', 'tipo')
            ->get()
            ->groupBy('articulo_id')
            ->map(fn ($filas) => $this->calcular($filas->pluck('total', 'tipo')->all()))
            ->all();
    }

    public function opciones(): array
    {
        return [
            'motivosEntrada' => config('inventario.motivos_entrada'),
            'motivosBaja' => config('inventario.motivos_baja'),
            'tiposDocumento' => config('inventario.tipos_documento'),
        ];
    }

    public function registrar(Articulo $articulo, array $data, int $userId): MovimientoInventario
    {
        return DB::transaction(function () use ($articulo, $data, $userId) {
            // Se bloquea el artículo: dos movimientos simultáneos no pueden pasarse del stock
            Articulo::lockForUpdate()->findOrFail($articulo->id);

            $disponibleAntes = $this->stock($articulo->id)['disponible'];

            if ($data['tipo'] === 'devolucion') {
                $prestamo = MovimientoInventario::prestamosVigentes()
                    ->where('articulo_id', $articulo->id)
                    ->findOrFail($data['movimiento_origen_id']);

                $devuelto = (int) $prestamo->devoluciones()->whereNull('anulado_at')->sum('cantidad');
                $pendiente = $prestamo->cantidad - $devuelto;

                if ($data['cantidad'] > $pendiente) {
                    throw ValidationException::withMessages([
                        'cantidad' => "De este préstamo solo quedan {$pendiente} por devolver.",
                    ]);
                }

                if ($data['fecha'] < $prestamo->fecha->toDateString()) {
                    throw ValidationException::withMessages([
                        'fecha' => 'La devolución no puede ser anterior al préstamo.',
                    ]);
                }
            }

            if (in_array($data['tipo'], ['baja', 'prestamo'], true) && $data['cantidad'] > $disponibleAntes) {
                throw ValidationException::withMessages([
                    'cantidad' => "Solo hay {$disponibleAntes} disponible(s) de este artículo.",
                ]);
            }

            $movimiento = new MovimientoInventario($data);
            $movimiento->articulo_id = $articulo->id;
            $movimiento->user_id = $userId; // siempre el usuario autenticado
            $movimiento->save();

            return $movimiento;
        });
    }

    public function anular(MovimientoInventario $movimiento, string $motivo, int $userId): void
    {
        DB::transaction(function () use ($movimiento, $motivo, $userId) {
            Articulo::lockForUpdate()->findOrFail($movimiento->articulo_id);

            $movimiento = MovimientoInventario::findOrFail($movimiento->id);

            if ($movimiento->anulado_at) {
                return; // ya estaba anulado
            }

            if ($movimiento->tipo === 'prestamo'
                && $movimiento->devoluciones()->whereNull('anulado_at')->exists()) {
                throw ValidationException::withMessages([
                    'motivo' => 'El préstamo tiene devoluciones registradas: anúlalas primero.',
                ]);
            }

            $movimiento->forceFill([
                'anulado_at' => now(),
                'anulado_por' => $userId,
                'motivo_anulacion' => $motivo,
            ])->save();

            // Si el stock queda negativo, se deshace todo
            $s = $this->stock($movimiento->articulo_id);

            if ($s['disponible'] < 0 || $s['en_prestamo'] < 0) {
                throw ValidationException::withMessages([
                    'motivo' => 'No se puede anular: dejaría el stock en negativo (por ejemplo, unidades ya prestadas o dadas de baja).',
                ]);
            }
        });
    }
}
