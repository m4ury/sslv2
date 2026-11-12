<?php

namespace App\Models;

use Database\Factories\ControlFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Control extends Model
{
    /** @use HasFactory<ControlFactory> */
    use HasFactory, softDeletes;

    protected $fillable = [
        'socio_id',
        'programado_id',
        'user_id',
        'fecha_control',
        'tipo_control',
        'hora_control',
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

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class);
    }

    public function programado(): BelongsTo
    {
        return $this->belongsTo(Programado::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mediciones(): HasMany
    {
        return $this->hasMany(Medicion::class);
    }

    public function prestaciones(): HasMany
    {
        return $this->hasMany(Prestacion::class);
    }

    protected function hora(): Attribute
    {
        return Attribute::get(fn (?string $valor) => $valor ? substr($valor, 0, 5) : null);
    }
}
