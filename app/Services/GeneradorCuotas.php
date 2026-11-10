<?php

namespace App\Services;

use App\Models\ConfigCuota;
use App\Models\Cuota;
use App\Models\Socio;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class GeneradorCuotas
{
    /**
     * Genera las cuotas mensuales de una configuración para los socios activos,
     * desde el inicio de la configuración (o el ingreso del socio, si es
     * posterior) hasta el mes indicado. Es idempotente.
     *
     * @return array{creadas: int, existentes: int}
     */
    public function generar(ConfigCuota $config, ?CarbonInterface $hasta = null): array
    {
        $inicio = $config->fecha_inicio->copy()->startOfMonth();
        $limite = ($hasta ?? now())->copy()->startOfMonth();

        if ($config->fecha_termino && $config->fecha_termino->copy()->startOfMonth()->lt($limite)) {
            $limite = $config->fecha_termino->copy()->startOfMonth();
        }

        $creadas = 0;
        $existentes = 0;

        Socio::where('estado', Socio::ESTADO_ACTIVO)->each(
            function (Socio $socio) use ($config, $inicio, $limite, &$creadas, &$existentes) {
                $desde = $inicio->copy();

                if ($socio->fecha_ingreso) {
                    $ingreso = Carbon::parse($socio->fecha_ingreso)->startOfMonth();
                    if ($ingreso->gt($desde)) {
                        $desde = $ingreso;
                    }
                }

                for ($mes = $desde->copy(); $mes->lte($limite); $mes = $mes->addMonth()) {
                    $cuota = Cuota::firstOrCreate(
                        [
                            'socio_id' => $socio->id,
                            'config_cuota_id' => $config->id,
                            'periodo' => $mes->toDateString(),
                        ],
                        [
                            'monto_cuota' => $config->monto, // se congela el monto vigente
                            'estado' => Cuota::PENDIENTE,
                            'fecha_vencimiento' => $this->vencimiento($mes, $config->dia_vencimiento),
                        ],
                    );

                    $cuota->wasRecentlyCreated ? $creadas++ : $existentes++;
                }
            },
        );

        return compact('creadas', 'existentes');
    }

    private function vencimiento(CarbonInterface $mes, int $dia): string
    {
        return $mes->copy()->day(min($dia, $mes->daysInMonth))->toDateString();
    }
}
