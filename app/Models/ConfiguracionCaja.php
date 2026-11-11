<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionCaja extends Model
{
    /** @use HasFactory<\Database\Factories\ConfiguracionCajaFactory> */
    use HasFactory;

    protected $table = 'configuracion_caja';

    protected $fillable = ['fecha_inicio', 'saldo_inicial'];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date:Y-m-d',
            'saldo_inicial' => 'integer',
        ];
    }
}
