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
        'categorias_ingreso' => [
        'Subvención municipal',
        'Otra subvención o fondo concursable',
        'Donaciones',
        'Actividades y eventos',
        'Venta de productos o servicios',
        'Otros ingresos',
    ],

    'tipos_documento_ingreso' => [
        'Comprobante de depósito',
        'Decreto o resolución',
        'Convenio',
        'Recibo',
        'Factura',
        'Sin documento',
    ],

    'tipos_documento' => ['Boleta', 'Factura', 'Recibo', 'Comprobante de transferencia', 'Sin documento'],

    // Si es false, un egreso no puede superar el saldo disponible
    'permitir_saldo_negativo' => false,
];
