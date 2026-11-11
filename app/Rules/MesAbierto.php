<?php

namespace App\Rules;

use App\Models\CierreCaja;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MesAbierto implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $cerrado = CierreCaja::fechaCerrada($value);
        } catch (\Throwable) {
            return; // fecha inválida: ya la reporta la regla "date"
        }

        if ($cerrado) {
            $fail('El mes de esa fecha ya está cerrado en caja; no se pueden registrar movimientos en él.');
        }
    }
}
