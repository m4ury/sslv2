<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdultoCuidador extends Model
{
    use SoftDeletes;

    protected $table = 'adulto_cuidador';

    protected $fillable = [
        'adulto_id', 'cuidador_id', 'vinculo', 'es_principal',
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

    // Un adulto tiene un solo cuidador principal vigente
    protected static function booted(): void
    {
        static::saved(function (self $registro) {
            if ($registro->es_principal && $registro->fecha_fin === null) {
                static::where('adulto_id', $registro->adulto_id)
                    ->where('id', '!=', $registro->id)
                    ->whereNull('fecha_fin')
                    ->update(['es_principal' => false]);
            }
        });
    }

    public function adulto()
    {
        return $this->belongsTo(Adulto::class);
    }

    public function cuidador()
    {
        return $this->belongsTo(Cuidador::class);
    }
}
