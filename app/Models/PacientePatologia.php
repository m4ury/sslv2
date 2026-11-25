<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PacientePatologia extends Model
{
    use SoftDeletes;

    protected $table = 'paciente_patologia';

    protected $fillable = ['paciente_id', 'patologia_id', 'fecha_diagnostico', 'estado', 'observaciones'];

    protected function casts(): array
    {
        return ['fecha_diagnostico' => 'date:Y-m-d'];
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function patologia()
    {
        return $this->belongsTo(Patologia::class);
    }
}
