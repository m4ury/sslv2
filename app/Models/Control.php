<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Control extends Model
{
    /** @use HasFactory<\Database\Factories\ControlFactory> */
    use HasFactory, softDeletes;

    protected $fillable = [
        'adulto_id',
        'programado_id',
        'user_id',
        'fecha_control',
        'tipo_control',
        'diagnostico_ingreso',
        'observacion',
        'indicaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_control' => 'date:Y-m-d',
        ];
    }

    public function adulto(): BelongsTo
    {
        return $this->belongsTo(Adulto::class);
    }

    public function programado(): BelongsTo
    {
        return $this->belongsTo(Programado::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicion(): HasOne
    {
        return $this->hasOne(Medicion::class);
    }

    public function prestaciones(): HasMany
    {
        return $this->hasMany(Prestacion::class);
    }
}
