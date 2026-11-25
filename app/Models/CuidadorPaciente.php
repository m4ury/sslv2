<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuidadorPaciente extends Model
{
    use SoftDeletes;

    protected $table = 'cuidador_paciente';

    protected $fillable = [
        'paciente_id', 'cuidador_id', 'vinculo', 'es_principal',
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

    // Un paciente tiene un solo cuidador principal vigente
    protected static function booted(): void
    {
        static::saved(function (self $registro) {
            if ($registro->es_principal && $registro->fecha_fin === null) {
                static::where('paciente_id', $registro->paciente_id)
                    ->where('id', '!=', $registro->id)
                    ->whereNull('fecha_fin')
                    ->update(['es_principal' => false]);
            }
        });
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function cuidador()
    {
        return $this->belongsTo(Cuidador::class);
    }
}
