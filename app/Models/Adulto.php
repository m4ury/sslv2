<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adulto extends Model
{
    /** @use HasFactory<\Database\Factories\AdultoFactory> */
    use HasFactory;

    protected $fillable = [
        'fecha_ingreso',
        'fecha_salida',
        'estado',
        'persona_id',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'observaciones',
    ];

    public const ESTADO_INICIAL = 'Activo';

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date:Y-m-d',
            'fecha_salida' => 'date:Y-m-d',
        ];
    }
}
