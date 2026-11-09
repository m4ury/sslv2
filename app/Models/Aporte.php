<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aporte extends Model
{
    /** @use HasFactory<\Database\Factories\AporteFactory> */
    use HasFactory;

    protected $fillable = ['fecha_aporte', 'monto', 'medio_pago', 'numero_comprobante', 'observacion'];

    protected function casts(): array
    {
        return [
            'fecha_aporte' => 'date:Y-m-d',
            'monto' => 'integer',
            'anulado_at' => 'datetime',
        ];
    }

    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class);
    }

    // Quien consignó el aporte en el sistema
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
