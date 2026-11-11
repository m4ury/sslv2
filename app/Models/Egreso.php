<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    /** @use HasFactory<\Database\Factories\EgresoFactory> */
    use HasFactory;

    // user_id y los datos de anulación los asigna solo el servidor
    protected $fillable = [
        'fecha_egreso', 'monto_egreso', 'categoria', 'descripcion_egreso', 'proveedor',
        'tipo_documento', 'num_documento', 'medio_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha_egreso' => 'date:Y-m-d',
            'monto_egreso' => 'integer',
            'anulado_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }
}
