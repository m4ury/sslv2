<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngresoBancario extends Model
{
    protected $table = 'ingresos_bancarios';

    protected $fillable = ['fecha', 'monto', 'concepto', 'descripcion', 'banco', 'num_operacion'];

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
