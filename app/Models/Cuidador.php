<?php

namespace App\Models;

use Database\Factories\CuidadorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TieneEstadoDeRol;

class Cuidador extends Model
{
    /** @use HasFactory<\Database\Factories\CuidadorFactory> */
    use HasFactory, TieneEstadoDeRol;

    protected $table = 'cuidadores';

    protected $fillable = [
        'persona_id', 'fecha_ingreso', 'fecha_salida', 'estado', 'telefono', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date:Y-m-d',
            'fecha_salida' => 'date:Y-m-d',
        ];
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    // Asignaciones con sus datos (las usa la pantalla del cuidador)
    public function asignaciones()
    {
        return $this->hasMany(CuidadorSocio::class);
    }

    // Acceso n:m directo a los socios
    public function socios()
    {
        return $this->belongsToMany(socio::class, 'cuidador_socio')
            ->wherePivotNull('deleted_at')
            ->withPivot('vinculo', 'es_principal', 'fecha_inicio', 'fecha_fin');
    }
}
