<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    /** @use HasFactory<\Database\Factories\SocioFactory> */
    use HasFactory;

    protected $fillable = [
        'fecha_ingreso',
        'fecha_salida',
        'estado',
        'persona_id',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public const ESTADO_INICIAL = 'Activo';
}
