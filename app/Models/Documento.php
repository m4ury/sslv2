<?php

namespace App\Models;

use Database\Factories\DocumentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    /** @use HasFactory<DocumentoFactory> */
    use HasFactory;

    protected $fillable = [
        'socio_id',
        'user_id',
        'tipo_documento_id',
        'nombre',
        'descripcion',
        'fecha_documento',
        'archivo_ruta',
        'archivo_original',
        'mime_type',
        'tamanio_bytes',
        'fecha_carga',
    ];

    protected $hidden = ['archivo_ruta'];

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(Tipo_documento::class, 'tipo_documento_id');
    }

    protected function casts(): array
    {
        return ['fecha_documento' => 'date:Y-m-d'];
    }
}
