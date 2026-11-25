<?php

namespace App\Models;

use App\Models\Concerns\TieneEstadoDeRol;
use Database\Factories\SocioFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    /** @use HasFactory<SocioFactory> */
    use HasFactory, TieneEstadoDeRol;

    protected $fillable = [
        'fecha_ingreso',
        'fecha_salida',
        'estado',
        'persona_id',
        'telefono',
        'observaciones',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date:Y-m-d',
            'fecha_salida' => 'date:Y-m-d',
        ];
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => trim(collect([
            $this->persona?->nombres,
            $this->persona?->apellido_paterno,
            $this->persona?->apellido_materno,
        ])->filter()->implode(' ')) ?: "Socio #{$this->id}");
    }
}
