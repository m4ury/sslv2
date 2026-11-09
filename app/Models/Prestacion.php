<?php

namespace App\Models;

use Database\Factories\PrestacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prestacion extends Model
{
    /** @use HasFactory<PrestacionFactory> */
    use HasFactory;

    protected $fillable = [
        'control_id',
        'fecha',
        'nombre_prestacion',
        'descripcion',
        'parametro',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
            'parametro' => 'float',
        ];
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }
}
