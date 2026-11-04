<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\TieneEstadoDeRol;

class Adulto extends Model
{
    /** @use HasFactory<\Database\Factories\AdultoFactory> */
    use HasFactory, TieneEstadoDeRol;

    protected $fillable = [
        'fecha_ingreso',
        'fecha_salida',
        'estado',
        'persona_id',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'observaciones',
    ];

    //public const ESTADO_INICIAL = 'activo';

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function controles()
    {
        return $this->hasMany(Control::class);
    }

    public function programados()
    {
        return $this->hasMany(Programado::class);
    }

    public function prestaciones()
    {
        return $this->hasMany(Prestacion::class);
    }

    public function credenciales()
    {
        return $this->hasMany(Credencial::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documentos::class);
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
        ])->filter()->implode(' ')) ?: "Adulto #{$this->id}");
    }
}
