@php
    $fmt = fn ($n) => ($n < 0 ? '-' : '').'$'.number_format(abs($n), 0, ',', '.');
    $fecha = fn ($f) => \Carbon\Carbon::parse($f)->format('d-m-Y');
    $r = $resumen;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de cuentas {{ $r['etiqueta'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { font-size: 17px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 20px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #888; }
        .sub { color: #555; margin-bottom: 4px; }
        .estado { margin: 6px 0 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; }
        th { background: #f0f0f0; font-size: 9px; text-transform: uppercase; }
        .r { text-align: right; }
        .total td { font-weight: bold; border-top: 1px solid #555; border-bottom: none; }
        .resumen td { font-size: 11px; padding: 5px 6px; }
        .final td { font-size: 12px; font-weight: bold; border-top: 2px solid #111; }
        .vacio { color: #777; padding: 8px 6px; }
        .pie { margin-top: 26px; color: #666; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ $agrupacion }}</h1>
    <div class="sub">Estado de cuentas · {{ $r['etiqueta'] }}</div>
    <div class="estado">
        @if ($cierre)
            MES CERRADO — por {{ $cierre->cerrador?->name ?? '—' }} el {{ $cierre->cerrado_at->format('d-m-Y H:i') }}
        @else
            ESTADO PROVISORIO — el mes aún no está cerrado y las cifras pueden cambiar
        @endif
    </div>

    <h2>Resumen</h2>
    <table class="resumen">
        <tr><td>Saldo inicial</td><td class="r">{{ $fmt($r['saldo_inicial']) }}</td></tr>
        <tr><td>(+) Cuotas pagadas por socios</td><td class="r">{{ $fmt($r['total_cuotas']) }}</td></tr>
        <tr><td>(+) Aportes de colaboradores</td><td class="r">{{ $fmt($r['total_aportes']) }}</td></tr>
        <tr><td>(−) Egresos</td><td class="r">{{ $fmt($r['total_egresos']) }}</td></tr>
        <tr class="final"><td>Saldo final</td><td class="r">{{ $fmt($r['saldo_final']) }}</td></tr>
    </table>

    <h2>Ingresos por cuotas ({{ count($r['cuotas']) }})</h2>
    @if (count($r['cuotas']))
        <table>
            <thead><tr><th>Fecha</th><th>Socio</th><th>RUT</th><th>Cuota</th><th>Medio</th><th>Comprobante</th><th class="r">Monto</th></tr></thead>
            <tbody>
            @foreach ($r['cuotas'] as $c)
                <tr>
                    <td>{{ $fecha($c['fecha_pago']) }}</td>
                    <td>{{ $c['socio'] }}</td>
                    <td>{{ $c['rut'] }}</td>
                    <td>{{ $c['periodo'] }}</td>
                    <td>{{ $c['medio_pago'] }}</td>
                    <td>{{ $c['comprobante'] ?? '—' }}</td>
                    <td class="r">{{ $fmt($c['monto_cuotas']) }}</td>
                </tr>
            @endforeach
            <tr class="total"><td colspan="6">Total cuotas</td><td class="r">{{ $fmt($r['total_cuotas']) }}</td></tr>
            </tbody>
        </table>
    @else
        <div class="vacio">Sin pagos de cuotas en el mes.</div>
    @endif

    <h2>Aportes de colaboradores ({{ count($r['aportes']) }})</h2>
    @if (count($r['aportes']))
        <table>
            <thead><tr><th>Fecha</th><th>Colaborador</th><th>RUT</th><th>Medio</th><th>Comprobante</th><th class="r">Monto</th></tr></thead>
            <tbody>
            @foreach ($r['aportes'] as $a)
                <tr>
                    <td>{{ $fecha($a['fecha_aporte']) }}</td>
                    <td>{{ $a['colaborador'] }}</td>
                    <td>{{ $a['rut'] }}</td>
                    <td>{{ $a['medio_pago'] ?? '—' }}</td>
                    <td>{{ $a['numero_comprobante'] ?? '—' }}</td>
                    <td class="r">{{ $fmt($a['monto_aportes']) }}</td>
                </tr>
            @endforeach
            <tr class="total"><td colspan="5">Total aportes</td><td class="r">{{ $fmt($r['total_aportes']) }}</td></tr>
            </tbody>
        </table>
    @else
        <div class="vacio">Sin aportes en el mes.</div>
    @endif

    <h2>Egresos por categoría</h2>
    @if (count($r['egresos_por_categoria']))
        <table>
            <thead><tr><th>Categoría</th><th class="r">Movimientos</th><th class="r">Total</th></tr></thead>
            <tbody>
            @foreach ($r['egresos_por_categoria'] as $cat)
                <tr><td>{{ $cat['categoria'] }}</td><td class="r">{{ $cat['cantidad'] }}</td><td class="r">{{ $fmt($cat['total']) }}</td></tr>
            @endforeach
            <tr class="total"><td colspan="2">Total egresos</td><td class="r">{{ $fmt($r['total_egresos']) }}</td></tr>
            </tbody>
        </table>
    @else
        <div class="vacio">Sin egresos en el mes.</div>
    @endif

    <h2>Detalle de egresos ({{ count($r['egresos']) }})</h2>
    @if (count($r['egresos']))
        <table>
            <thead><tr><th>Fecha</th><th>Categoría</th><th>Descripción</th><th>Proveedor</th><th>Documento</th><th class="r">Monto</th></tr></thead>
            <tbody>
            @foreach ($r['egresos'] as $e)
                <tr>
                    <td>{{ $fecha($e['fecha_egreso']) }}</td>
                    <td>{{ $e['categoria'] }}</td>
                    <td>{{ $e['descripcion_egreso'] }}</td>
                    <td>{{ $e['proveedor'] ?? '—' }}</td>
                    <td>{{ $e['documento'] ?: '—' }}</td>
                    <td class="r">{{ $fmt($e['monto_egresos']) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <div class="pie">
        Generado por {{ $generadoPor }} el {{ $generadoEn->format('d-m-Y H:i') }}.
        Estado en base a caja: considera solo los ingresos recibidos y los egresos pagados en el mes.
    </div>
</body>
</html>
