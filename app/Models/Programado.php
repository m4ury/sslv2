<?php

namespace App\Models;

use Database\Factories\ProgramadoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Programado extends Model
{
    /** @use HasFactory<ProgramadoFactory> */
    use HasFactory;

    protected $fillable = [
        'socio_id',
        'fecha_programada',
        'hora',
        'duracion_min',
        'tipo_control',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'date:Y-m-d',
            'duracion_min' => 'integer',
        ];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    /** Control realizado a partir de esta hora programada */
    public function control(): HasOne
    {
        return $this->hasOne(Control::class);
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'Pendiente');
    }

    // El campo TIME llega como "09:30:00": se entrega como "09:30"
    protected function hora(): Attribute
    {
        return Attribute::get(fn (?string $valor) => $valor ? substr($valor, 0, 5) : null);
    }
}
