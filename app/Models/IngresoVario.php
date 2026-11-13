<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngresoVario extends Model
{
    protected $table = 'ingresos_varios';

    // user_id y los datos de anulación los asigna solo el servidor
    protected $fillable = [
        'fecha', 'monto', 'categoria', 'descripcion', 'origen',
        'tipo_documento', 'num_documento', 'medio_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
            'monto' => 'integer',
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
