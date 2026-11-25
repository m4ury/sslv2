<?php

namespace App\Models;

use App\Models\Concerns\TieneEstadoDeRol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Paciente extends Model
{
    use TieneEstadoDeRol;

    // public const ESTADO_INICIAL = 'Activo';

    protected $fillable = [
        'persona_id',
        'fecha_ingreso',
        'fecha_salida',
        'estado',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'observaciones',
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

    // Registros con sus datos (los usa la ficha clínica)
    public function patologias()
    {
        return $this->hasMany(PacientePatologia::class);
    }

    // Acceso n:m directo al catálogo
    public function diagnosticos()
    {
        return $this->belongsToMany(Patologia::class, 'paciente_patologia')
            ->wherePivotNull('deleted_at')
            ->withPivot('estado', 'fecha_diagnostico');
    }

    public function controles(): HasMany
    {
        return $this->hasMany(Control::class);
    }

    public function prestaciones(): HasManyThrough
    {
        return $this->hasManyThrough(Prestacion::class, Control::class);
    }

    public function programados(): HasMany
    {
        return $this->hasMany(Programado::class);
    }

    public function credenciales(): HasMany
    {
        return $this->hasMany(Credencial::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function cuidadores(): HasMany
    {
        return $this->hasMany(CuidadorPaciente::class);
    }
}
