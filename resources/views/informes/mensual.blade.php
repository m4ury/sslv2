@php
    $fmt = fn ($n) => ($n < 0 ? '-' : '').'$'.number_format(abs($n), 0, ',', '.');
    $fecha = fn ($f) => \Carbon\Carbon::parse($f)->format('d-m-Y');
    $celda = function ($col, $fila) use ($fmt, $fecha) {
        $v = $fila[$col['key']] ?? null;

        if ($v === null || $v === '') {
            return '—';
        }

        return match ($col['tipo'] ?? 'texto') {
            'fecha' => $fecha($v),
            'dinero' => $fmt($v),
            default => $v,
        };
    };
    $i = $informe;
    $span = count($i['columnas']) - 1;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $i['titulo'] }} {{ $i['etiqueta'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { font-size: 17px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 20px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #888; }
        .sub { color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; }
        th { background: #f0f0f0; font-size: 9px; text-transform: uppercase; }
        .r { text-align: right; }
        .total td { font-weight: bold; border-top: 1px solid #555; border-bottom: none; }
        .vacio { color: #777; padding: 8px 6px; }
        .pie { margin-top: 26px; color: #666; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ $agrupacion }}</h1>
    <div class="sub">{{ $i['titulo'] }} · {{ $i['etiqueta'] }}</div>

    <h2>Resumen</h2>
    <table>
        <tr><td>Movimientos</td><td class="r">{{ $i['cantidad'] }}</td></tr>
        <tr class="total"><td>Total del mes</td><td class="r">{{ $fmt($i['total']) }}</td></tr>
    </table>

    @foreach ($i['agrupaciones'] as $a)
        <h2>{{ $a['titulo'] }}</h2>
        @if (count($a['filas']))
            <table>
                <thead><tr><th>Nombre</th><th class="r">Movimientos</th><th class="r">Total</th></tr></thead>
                <tbody>
                @foreach ($a['filas'] as $f)
                    <tr><td>{{ $f['nombre'] }}</td><td class="r">{{ $f['cantidad'] }}</td><td class="r">{{ $fmt($f['total']) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="vacio">Sin movimientos en el mes.</div>
        @endif
    @endforeach

    <h2>Detalle</h2>
    @if (count($i['filas']))
        <table>
            <thead>
                <tr>
                @foreach ($i['columnas'] as $col)
                    <th class="{{ ($col['tipo'] ?? '') === 'dinero' ? 'r' : '' }}">{{ $col['label'] }}</th>
                @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach ($i['filas'] as $fila)
                <tr>
                @foreach ($i['columnas'] as $col)
                    <td class="{{ ($col['tipo'] ?? '') === 'dinero' ? 'r' : '' }}">{{ $celda($col, $fila) }}</td>
                @endforeach
                </tr>
            @endforeach
            <tr class="total"><td colspan="{{ $span }}">Total</td><td class="r">{{ $fmt($i['total']) }}</td></tr>
            </tbody>
        </table>
    @else
        <div class="vacio">Sin movimientos en el mes.</div>
    @endif

    <div class="pie">
        Documento reservado. Generado por {{ $generadoPor }} el {{ $generadoEn->format('d-m-Y H:i') }}.
    </div>
</body>
</html>