<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'tipo', 'motivo', 'cantidad', 'fecha', 'origen', 'valor_unitario',
        'tipo_documento', 'num_documento', 'prestatario', 'prestatario_telefono',
        'fecha_compromiso', 'movimiento_origen_id', 'observaciones',
    ];

    // Unidades ya devueltas de un préstamo (solo devoluciones vigentes)
    private const SQL_DEVUELTO = '(SELECT COALESCE(SUM(d.cantidad), 0) FROM movimientos_inventario AS d
        WHERE d.movimiento_origen_id = movimientos_inventario.id AND d.anulado_at IS NULL)';

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'valor_unitario' => 'integer',
            'fecha' => 'date:Y-m-d',
            'fecha_compromiso' => 'date:Y-m-d',
            'anulado_at' => 'datetime',
        ];
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // "anulador" y no "anuladoPor", para no chocar con la columna al serializar
    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    // Devoluciones de este préstamo
    public function devoluciones()
    {
        return $this->hasMany(self::class, 'movimiento_origen_id');
    }

    public function scopePrestamosVigentes(Builder $q): Builder
    {
        return $q->where('tipo', 'prestamo')->whereNull('anulado_at');
    }

    // Préstamos con unidades aún por devolver
    public function scopePendientes(Builder $q): Builder
    {
        return $q->prestamosVigentes()->whereRaw('cantidad > '.self::SQL_DEVUELTO);
    }

    public function scopeDevueltos(Builder $q): Builder
    {
        return $q->prestamosVigentes()->whereRaw('cantidad <= '.self::SQL_DEVUELTO);
    }

    // Requiere cargar antes withSum(['devoluciones as devuelto' => ...], 'cantidad')
    public function pendiente(): int
    {
        return $this->cantidad - (int) ($this->devuelto ?? 0);
    }

    public function estadoPrestamo(): string
    {
        if ($this->pendiente() <= 0) {
            return 'Devuelto';
        }

        return $this->fecha_compromiso && $this->fecha_compromiso->lt(today()) ? 'Vencido' : 'Vigente';
    }
}
