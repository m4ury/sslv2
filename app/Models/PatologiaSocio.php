<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PatologiaSocio extends Model
{
    use SoftDeletes;

    protected $table = 'patologia_socio';

    protected $fillable = ['socio_id', 'patologia_id', 'fecha_diagnostico', 'observaciones', 'estado'];

    protected function casts(): array
    {
        return ['fecha_diagnostico' => 'date:Y-m-d'];
    }

    public function socio()
    {
        return $this->belongsTo(Socio::class);
    }

    public function patologia()
    {
        return $this->belongsTo(Patologia::class);
    }
}
