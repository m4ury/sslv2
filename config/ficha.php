<?php

return [
    // min/max son límites de plausibilidad para evitar errores de digitación,
    // no umbrales clínicos.
    'mediciones' => [
        'pa_sistolica' => ['label' => 'PA sistólica', 'unidad' => 'mmHg', 'min' => 40, 'max' => 300],
        'pa_diastolica' => ['label' => 'PA diastólica', 'unidad' => 'mmHg', 'min' => 20, 'max' => 200],
        'frecuencia_cardiaca' => ['label' => 'Frecuencia cardíaca', 'unidad' => 'lpm', 'min' => 20, 'max' => 250],
        'saturacion' => ['label' => 'Saturación O₂', 'unidad' => '%', 'min' => 50, 'max' => 100],
        'temperatura' => ['label' => 'Temperatura', 'unidad' => '°C', 'min' => 30, 'max' => 45],
        'glicemia' => ['label' => 'Glicemia', 'unidad' => 'mg/dL', 'min' => 20, 'max' => 800],
        'peso' => ['label' => 'Peso', 'unidad' => 'kg', 'min' => 10, 'max' => 300],
        'talla' => ['label' => 'Talla', 'unidad' => 'cm', 'min' => 80, 'max' => 230],
    ],

    'tipos_control' => [
        'Control médico',
        'Control de enfermería',
        'Control kinésico',
        'Control nutricional',
        'Control psicológico',
        'Otro',
    ],
    'estados_patologia' => ['Activa', 'Controlada', 'Resuelta'],
    'estados_programado' => ['Pendiente', 'Realizado', 'Cancelado', 'No asistió'],
    'tipos_credencial' => ['Credencial de discapacidad', 'Carnet de control', 'Seguro de salud', 'Otra'],
    'tipos_documento' => ['Examen', 'Epicrisis', 'Receta', 'Informe', 'Consentimiento', 'Otro'],
];

