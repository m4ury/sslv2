<?php

namespace App\Models;

use Database\Factories\ColaboradorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Colaborador extends Model
{
    /** @use HasFactory<ColaboradorFactory> */
    use HasFactory;

    protected $table = 'colaboradores';

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

    public function aportes()
    {
        return $this->hasMany(Aporte::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public const ESTADO_INICIAL = 'Activo';
}
