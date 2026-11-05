<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdultoPatologia extends Model
{
    use SoftDeletes;

    protected $table = 'adulto_patologia';

    protected $fillable = ['adulto_id', 'patologia_id', 'fecha_diagnostico', 'observaciones'];

    protected function casts(): array
    {
        return ['fecha_diagnostico' => 'date:Y-m-d'];
    }

    public function adulto()
    {
        return $this->belongsTo(Adulto::class);
    }

    public function patologia()
    {
        return $this->belongsTo(Patologia::class);
    }
}
