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
        return $this->hasMany(AdultoPatologia::class);
    }

    // Adultos que tienen esta patología (útil para reportes)
    public function adultos()
    {
        return $this->belongsToMany(Adulto::class, 'adulto_patologia')
            ->wherePivotNull('deleted_at')
            ->withPivot('estado', 'fecha_diagnostico');
    }
}
