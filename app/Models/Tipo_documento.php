<?php

namespace App\Models;

use Database\Factories\TipoDocumentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo_documento extends Model
{
    /** @use HasFactory<TipoDocumentoFactory> */
    use HasFactory;

    protected $table = 'tipo_documentos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}
