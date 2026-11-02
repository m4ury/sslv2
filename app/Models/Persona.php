<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Appends(['full_name', 'edad'])]
class Persona extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombres',
        'rut',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'fecha_nacimiento',
        'sexo',
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
}
