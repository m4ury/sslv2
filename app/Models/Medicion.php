<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicion extends Model
{
    /** @use HasFactory<\Database\Factories\MedicionFactory> */
    use HasFactory;

    protected $fillable = [
        'control_id',
        /* 'presion_sistolica',
        'presion_diastolica',
        'hemoglobina_glicosilada',
        'peso_actual',
        'talla_actual',
        'imc',
        'clasificacion_imc', */
        'tipo',
        'valor',
        'unidad'
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'float'
            /* 'presion_sistolica' => 'float',
            'presion_diastolica' => 'float',
            'hemoglobina_glicosilada' => 'float',
            'peso_actual' => 'float',
            'talla_actual' => 'float',
            'imc' => 'float', */
        ];
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }

    public static function calcularImc(?float $peso, ?float $tallaCm): ?float
    {
        if (! $peso || ! $tallaCm) {
            return null;
        }

        $tallaM = $tallaCm / 100;

        return round($peso / ($tallaM * $tallaM), 2);
    }

    /**
     * Clasificación de IMC para personas mayores (criterio MINSAL / EMPAM).
     */
    public static function clasificarImc(?float $imc): ?string
    {
        if ($imc === null) {
            return null;
        }

        return match (true) {
            $imc < 23 => 'Bajo peso',
            $imc < 28 => 'Normal',
            $imc < 32 => 'Sobrepeso',
            default => 'Obesidad',
        };
    }
}
