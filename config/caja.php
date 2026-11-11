<?php

return [
    'categorias_egreso' => [
        'Servicios básicos',
        'Arriendo',
        'Insumos y materiales',
        'Alimentación',
        'Honorarios y remuneraciones',
        'Mantención y reparaciones',
        'Transporte',
        'Actividades y eventos',
        'Impuestos y trámites',
        'Otros',
    ],

    'tipos_documento' => ['Boleta', 'Factura', 'Recibo', 'Comprobante de transferencia', 'Sin documento'],

    // Si es false, un egreso no puede superar el saldo disponible
    'permitir_saldo_negativo' => false,
];
