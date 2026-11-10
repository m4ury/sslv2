<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TieneEstadoDeRol;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Socio extends Model
{
    /** @use HasFactory<\Database\Factories\SocioFactory> */
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

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }

    public function controles()
    {
        return $this->hasMany(Control::class);
    }

    public function programados()
    {
        return $this->hasMany(Programado::class);
    }

    public function prestaciones(): HasManyThrough
    {
        return $this->hasManyThrough(Prestacion::class, Control::class);
    }

    public function credenciales()
    {
        return $this->hasMany(Credencial::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function patologias()
    {
        return $this->hasMany(PatologiaSocio::class);
    }

    // Acceso n:m directo al catálogo
    public function diagnosticos()
    {
        return $this->belongsToMany(Patologia::class, 'patologia_socio')
            ->wherePivotNull('deleted_at')
            ->withPivot('estado', 'fecha_diagnostico');
    }

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date:Y-m-d',
            'fecha_salida' => 'date:Y-m-d',
        ];
    }

    public function cuidadores()
    {
        return $this->hasMany(CuidadorSocio::class);
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
