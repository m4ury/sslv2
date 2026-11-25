<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuidadorSocio extends Model
{
    protected $fillable = [
        'socio_id', 'cuidador_id', 'vinculo', 'es_principal',
        'fecha_inicio', 'fecha_fin', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'es_principal' => 'boolean',
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
        ];
    }
}
