<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CierreCaja extends Model
{
    /** @use HasFactory<\Database\Factories\CierreCajaFactory> */
    use HasFactory;

    protected $table = 'cierres_caja';

    protected $fillable = [
        'periodo', 'saldo_inicial', 'total_cuotas', 'total_aportes', 'total_egresos', 'saldo_final',
    ];

    protected function casts(): array
    {
        return ['cerrado_at' => 'datetime'];
    }

    public function cerrador()
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    // ¿El mes de esa fecha ya está cerrado?
    public static function fechaCerrada(mixed $fecha): bool
    {
        return static::where('periodo', Carbon::parse($fecha)->format('Y-m'))->exists();
    }
}
