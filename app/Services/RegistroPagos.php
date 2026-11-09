<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistroPagos
{
    public function registrar(Cuota $cuota, array $data, int $userId): Pago
    {
        return DB::transaction(function () use ($cuota, $data, $userId) {
            $cuota = Cuota::lockForUpdate()->findOrFail($cuota->id);

            $pagado = (int) $cuota->pagos()->whereNull('anulado_at')->sum('monto_pagado');
            $saldo = $cuota->monto_cuota - $pagado;

            if ($data['monto_pagado'] > $saldo) {
                throw ValidationException::withMessages([
                    'monto_pagado' => $saldo <= 0
                        ? 'La cuota ya está pagada.'
                        : 'El monto excede el saldo pendiente ($'.number_format($saldo, 0, ',', '.').').',
                ]);
            }

            $pago = new Pago($data);
            $pago->user_id = $userId; // siempre el usuario autenticado
            $cuota->pagos()->save($pago);

            $cuota->recalcularEstado();

            return $pago;
        });
    }

    public function anular(Pago $pago, string $motivo, int $userId): void
    {
        DB::transaction(function () use ($pago, $motivo, $userId) {
            $pago = Pago::lockForUpdate()->findOrFail($pago->id);

            if ($pago->anulado_at) {
                return; // ya estaba anulado
            }

            $pago->forceFill([
                'anulado_at' => now(),
                'anulado_por' => $userId,
                'motivo_anulacion' => $motivo,
            ])->save();

            Cuota::lockForUpdate()->findOrFail($pago->cuota_id)->recalcularEstado();
        });
    }
}
