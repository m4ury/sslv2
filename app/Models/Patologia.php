<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patologia extends Model
{
    /** @use HasFactory<\Database\Factories\PatologiaFactory> */
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion'];

    public function registros()
    {
        return $this->hasMany(PatologiaSocio::class);
    }

    // Socios que tienen esta patología (útil para reportes)
    public function socios()
    {
        return $this->belongsToMany(Socio::class, 'patologia_socio')
            ->wherePivotNull('deleted_at')
            ->withPivot('estado', 'fecha_diagnostico');
    }
}
