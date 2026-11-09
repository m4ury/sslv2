<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use App\Models\ConfigCuota;
use App\Services\GeneradorCuotas;
use Illuminate\Console\Command;

#[Signature('app:generar-cuotas')]
#[Description('Command description')]

class GenerarCuotas extends Command
{
    protected $signature = 'cuotas:generar {config? : ID de una configuración (por defecto, todas las activas)}';

    protected $description = 'Genera las cuotas mensuales de los socios activos';

    /**
     * Execute the console command.
     */
    public function handle(GeneradorCuotas $generador):int
    {
        $configs = ConfigCuota::where('activa', true)
            ->when($this->argument('config'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        foreach ($configs as $config) {
            $r = $generador->generar($config);
            $this->info("{$config->nombre}: {$r['creadas']} creadas, {$r['existentes']} ya existían.");
        }

        return self::SUCCESS;
    }
}
