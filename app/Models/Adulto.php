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
        'contacto_emergencia',
        'telefono_emergencia',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
