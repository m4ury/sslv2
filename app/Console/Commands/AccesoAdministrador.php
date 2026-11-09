<?php

namespace App\Console\Commands;

use App\Models\Profesion;
use App\Models\User;
use Illuminate\Console\Command;

class AccesoAdministrador extends Command
{
    protected $signature = 'acceso:administrador {email : Correo del usuario}';

    protected $description = 'Convierte a un usuario en administrador';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No existe un usuario con ese correo.');

            return self::FAILURE;
        }

        $profesion = Profesion::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['es_admin' => true, 'permisos' => []],
        );

        if (! $profesion->es_admin) {
            $profesion->update(['es_admin' => true]);
        }

        $user->forceFill(['profesion_id' => $profesion->id, 'activo' => true])->save();

        $this->info("{$user->email} ahora es administrador.");

        return self::SUCCESS;
    }
}