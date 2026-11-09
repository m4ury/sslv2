<?php

namespace App\Support;

use App\Models\User;

class Permisos
{
    /** @return array<string, string> clave => descripción */
    public static function todos(): array
    {
        return collect(config('permisos.grupos'))->collapse()->all();
    }

    public static function claves(): array
    {
        return array_keys(self::todos());
    }

    public static function existe(string $permiso): bool
    {
        return array_key_exists($permiso, self::todos());
    }

    /** Mapa clave => bool que se comparte con el frontend */
    public static function paraUsuario(?User $user): array
    {
        return collect(self::claves())
            ->mapWithKeys(fn ($p) => [$p => $user?->tienePermiso($p) ?? false])
            ->all();
    }
}