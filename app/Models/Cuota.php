<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    /** @use HasFactory<\Database\Factories\CuotaFactory> */
    use HasFactory;

    public const PENDIENTE = 'Pendiente';
    public const PARCIAL = 'Parcial';
    public const PAGADA = 'Pagada';

    protected $fillable = [
        'socio_id', 'config_cuota_id', 'periodo', 'monto_cuota', 'estado', 'fecha_vencimiento',
    ];

    protected $appends = ['pagado', 'saldo', 'estado_efectivo'];

    protected function casts(): array
    {
        return [
            'monto_cuota' => 'integer',
            'fecha_vencimiento' => 'date:Y-m-d',
        ];
    }

    public function socio()
    {
        return $this->belongsTo(Socio::class);
    }

    public function configuracion()
    {
        return $this->belongsTo(ConfigCuota::class, 'config_cuota_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    // Suma de pagos vigentes (los anulados no cuentan)
    protected function pagado(): Attribute
    {
        return Attribute::get(function () {
            $pagos = $this->relationLoaded('pagos') ? $this->pagos : $this->pagos()->get();

            return (int) $pagos->whereNull('anulado_at')->sum('monto_pagado');
        });
    }

    protected function saldo(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->monto_cuota - $this->pagado));
    }

    // "Vencida" se calcula: no se guarda en la base
    protected function estadoEfectivo(): Attribute
    {
        return Attribute::get(function () {
            $vencida = in_array($this->estado, [self::PENDIENTE, self::PARCIAL], true)
                && $this->fecha_vencimiento?->lt(today());

            return $vencida ? 'Vencida' : $this->estado;
        });
    }

    public function recalcularEstado(): void
    {
        $pagado = (int) $this->pagos()->whereNull('anulado_at')->sum('monto_pagado');

        $this->estado = match (true) {
            $pagado <= 0 => self::PENDIENTE,
            $pagado >= $this->monto_cuota => self::PAGADA,
            default => self::PARCIAL,
        };

        $this->save();
        $this->unsetRelation('pagos');
    }
}
