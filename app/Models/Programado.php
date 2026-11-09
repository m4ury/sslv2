<?php

namespace App\Models;

use Database\Factories\ProgramadoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Programado extends Model
{
    /** @use HasFactory<ProgramadoFactory> */
    use HasFactory;

    protected $fillable = [
        'adulto_id',
        'fecha_programada',
        'tipo_control',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'date:Y-m-d',
        ];
    }

    public function adulto(): BelongsTo
    {
        return $this->belongsTo(Adulto::class);
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
}
