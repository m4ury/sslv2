<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Appends(['full_name', 'edad'])]
class Persona extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'nombres',
        'rut',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'comuna',
        'telefono',
    ];

    public function getFullNameAttribute()
    {
        return strtoupper("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function getEdadAttribute()
    {
        if ($this->fecha_nacimiento) {
            return Carbon::parse($this->fecha_nacimiento)->age;
        }

        return null;
    }

    public function socio()
    {
        return $this->hasOne(Socio::class);
    }

    public function adulto()
    {
        return $this->hasOne(Adulto::class);
    }

    public function colaborador()
    {
        return $this->hasOne(Colaborador::class);
    }
}
