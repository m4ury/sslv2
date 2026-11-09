<?php

namespace App\Models;

use Database\Factories\CredencialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credencial extends Model
{
    /** @use HasFactory<CredencialFactory> */
    use HasFactory;

    protected $fillable = [
        'adulto_id',
        'fecha_emision',
        'fecha_expiracion',
        'numero',
        'estado',
        'porcentaje_discapacidad',
        'tipo_discapacidad',
        'organismo_emisor',
        'archivo_ruta',
        'observaciones',
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
