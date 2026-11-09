<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    /** @use HasFactory<\Database\Factories\PagoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['cuota_id', 'fecha_pago', 'monto_pagado', 'medio_pago', 'numero_comprobante', 'observacion'];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'date:Y-m-d',
            'monto_pagado' => 'integer',
            'anulado_at' => 'datetime',
        ];
    }

    public function cuota()
    {
        return $this->belongsTo(Cuota::class);
    }

    // Quien consignó el pago en el sistema
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // "anulador" y no "anuladoPor", para no chocar con la columna al serializar
    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }
}
