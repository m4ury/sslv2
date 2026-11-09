<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigCuota extends Model
{
    protected $table = 'config_cuotas';

    protected $fillable = ['nombre_cuota', 'monto', 'fecha_inicio', 'fecha_termino', 'dia_vencimiento', 'activa'];

    protected function casts(): array
    {
        return [
            'monto' => 'integer',
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_termino' => 'date:Y-m-d',
            'dia_vencimiento' => 'integer',
            'activa' => 'boolean',
        ];
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'config_cuota_id');
    }
}
