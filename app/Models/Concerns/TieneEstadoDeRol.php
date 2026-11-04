<?php

namespace App\Models\Concerns;

trait TieneEstadoDeRol
{
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_INACTIVO = 'inactivo';
    public const ESTADO_INICIAL = 'activo';

    public function estaActivo(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    public function estaInactivo(): bool
    {
        return $this->estado === self::ESTADO_INACTIVO;
    }

    public function darDeBaja(): void
    {
        $this->update([
            'estado' => self::ESTADO_INACTIVO,
            'fecha_salida' => now()->toDateString(),
        ]);
    }

    public function reactivar(): void
    {
        $this->update([
            'estado' => self::ESTADO_ACTIVO,
            'fecha_salida' => null,
        ]);
    }
}