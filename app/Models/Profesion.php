<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesion extends Model
{
    /** @use HasFactory<\Database\Factories\ProfesionFactory> */
    use HasFactory;

    protected $table = 'profesiones';

    protected $fillable = ['nombre', 'permisos', 'es_admin'];

    protected function casts(): array
    {
        return [
            'permisos' => 'array',
            'es_admin' => 'boolean',
        ];
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
