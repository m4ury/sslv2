<?php

return [
    'grupos' => [
        'Personas y roles' => [
            'personas.ver' => 'Ver personas',
            'personas.gestionar' => 'Crear, editar y eliminar personas',
            'roles.gestionar' => 'Registrar y editar socios, colaboradores y cuidadores',
        ],
        'Ficha clínica' => [
            'ficha.ver' => 'Ver la ficha clínica del socio',
            'ficha.registrar' => 'Registrar y editar controles, prestaciones, patologías, controles programados, credenciales y documentos',
            'patologias.catalogo' => 'Agregar y editar patologías del catálogo',
            'cuidadores.asignar' => 'Asignar cuidadores a un paciente',
        ],
        'Tesorería' => [
            'cuotas.ver' => 'Ver cuotas',
            'cuotas.pagar' => 'Registrar y anular pagos de cuotas',
            'cuotas.configurar' => 'Configurar cuotas y generarlas',
            'aportes.ver' => 'Ver aportes de colaboradores',
            'aportes.registrar' => 'Registrar y anular aportes',
            'caja.ver' => 'Ver el flujo de caja y los estados de cuentas',
            'caja.registrar' => 'Registrar y anular egresos',
            'caja.cerrar' => 'Cerrar y reabrir meses, y definir el saldo inicial de caja',
        ],
        'Administración' => [
            'usuarios.gestionar' => 'Administrar usuarios',
            'profesiones.gestionar' => 'Administrar profesiones y sus permisos',
        ],
    ],

    // Atajos para marcar varios permisos a la vez en el mantenedor de profesiones
    'plantillas' => [
        'Clínico' => ['personas.ver', 'ficha.ver', 'ficha.registrar', 'patologias.catalogo'],
        'Tesorería' => [
            'personas.ver', 'cuotas.ver', 'cuotas.pagar', 'cuotas.configurar',
            'aportes.ver', 'aportes.registrar',
            'caja.ver', 'caja.registrar', 'caja.cerrar',
        ],
        'Secretaría' => ['personas.ver', 'personas.gestionar', 'roles.gestionar', 'cuotas.ver', 'aportes.ver'],
    ],
];
