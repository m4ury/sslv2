<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    protected $table = 'articulos';

    protected $fillable = [
        'codigo', 'nombre', 'categoria', 'unidad', 'ubicacion', 'stock_minimo', 'descripcion', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'stock_minimo' => 'integer',
        ];
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}
