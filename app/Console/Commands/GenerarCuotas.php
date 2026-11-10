<?php

namespace App\Console\Commands;

use App\Models\ConfigCuota;
use App\Services\GeneradorCuotas;
use Illuminate\Console\Command;

class GenerarCuotas extends Command
{
    protected $signature = 'cuotas:generar {config? : ID de una configuración (por defecto, todas las activas)}';

    protected $description = 'Genera las cuotas mensuales de los socios activos';

    /**
     * Execute the console command.
     */
    public function handle(GeneradorCuotas $generador): int
    {
        $configs = ConfigCuota::where('activa', true)
            ->when($this->argument('config'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        if ($configs->isEmpty()) {
            if ($this->argument('config') !== null) {
                $this->error('No se encontró una configuración activa con ese ID.');

                return self::FAILURE;
            }

            $this->warn('No hay configuraciones de cuotas activas para procesar.');

            return self::SUCCESS;
        }

        foreach ($configs as $config) {
            $r = $generador->generar($config);
            $creadas = $r['creadas'] === 1 ? 'cuota creada' : 'cuotas creadas';
            $existentes = $r['existentes'] === 1 ? 'cuota ya existente' : 'cuotas ya existentes';

            $this->info("{$config->nombre_cuota}: {$r['creadas']} {$creadas}, {$r['existentes']} {$existentes}.");
        }

        return self::SUCCESS;
    }
}
