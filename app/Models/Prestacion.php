<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestacion extends Model
{
    /** @use HasFactory<\Database\Factories\PrestacionFactory> */
    use HasFactory;

    protected $fillable = [
        'control_id',
        'nombre_prestacion',
        'descripcion',
        'parametro',
    ];

    protected function casts(): array
    {
        return [
            'parametro' => 'float',
        ];
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }
}
