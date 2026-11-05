<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentoFactory> */
    use HasFactory;

    protected $fillable = [
        'adulto_id',
        'user_id',
        'tipo_documento_id',
        'nombre',
        'descripcion',
        'fecha_documento',
        'archivo_ruta',
        'archivo_original',
        'mime_type',
        'tamano_bytes',
        'fecha_carga'
        ];

    protected $hidden = ['archivo_ruta'];

    protected function casts(): array
    {
        return ['fecha_documento' => 'date:Y-m-d'];
    }
}
