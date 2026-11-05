<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credencial extends Model
{
    /** @use HasFactory<\Database\Factories\CredencialFactory> */
    use HasFactory;

    protected $fillable = [
        'adulto_id',
        'numero_credencial',
        'fecha_emision',
        'fecha_expiracion',
        'numero',
        'estado',
        'grado_discapacidad',
        'porcentaje_discapacidad',
        'tipo_discapacidad',
        'organismo_emisor',
        'archivo_ruta',
        'observaciones'
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date:Y-m-d',
            'fecha_expiracion' => 'date:Y-m-d',
        ];
    }

    public function adulto()
    {
        return $this->belongsTo(Adulto::class);
    }
}
