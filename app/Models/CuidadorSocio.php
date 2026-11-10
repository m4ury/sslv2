<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CuidadorSocio extends Model
{
    protected $table = 'cuidador_socio';

    protected $fillable = [
        'socio_id', 'cuidador_id', 'vinculo', 'es_principal',
        'fecha_inicio', 'fecha_fin', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'es_principal' => 'boolean',
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
        ];
    }

    // Un socio tiene un solo cuidador principal vigente
    protected static function booted(): void
    {
        static::saved(function (self $registro) {
            if ($registro->es_principal && $registro->fecha_fin === null) {
                static::where('socio_id', $registro->socio_id)
                    ->where('id', '!=', $registro->id)
                    ->whereNull('fecha_fin')
                    ->update(['es_principal' => false]);
            }
        });
    }

    public function socio()
    {
        return $this->belongsTo(Socio::class);
    }

    public function cuidador()
    {
        return $this->belongsTo(Cuidador::class);
    }
}
