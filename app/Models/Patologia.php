<?php

namespace App\Models;

use Database\Factories\PatologiaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patologia extends Model
{
    /** @use HasFactory<PatologiaFactory> */
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion'];

    public function registros()
    {
        return $this->hasMany(PacientePatologia::class);
    }

    // Pacientes que tienen esta patología (útil para reportes)
    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'paciente_patologia')
            ->wherePivotNull('deleted_at')
            ->withPivot('estado', 'fecha_diagnostico');
    }
}
