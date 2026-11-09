export const cuidadorSections = [
    {
        title: 'Pertenencia a la agrupación',
        fields: [
            {
                name: 'fecha_ingreso',
                label: 'Fecha de ingreso',
                type: 'date',
                required: true,
            },
            {
                name: 'fecha_salida',
                label: 'Fecha de salida',
                type: 'date',
                minFrom: 'fecha_ingreso',
                editOnly: true,
            },
            {
                name: 'estado',
                label: 'Estado',
                type: 'select',
                required: true,
                editOnly: true,
                options: ['Activo', 'Inactivo'],
            },
        ],
    },
    {
        title: 'Contacto',
        fields: [
            {
                name: 'telefono',
                label: 'Teléfono',
                type: 'tel',
                required: true,
                placeholder: '+56 9 1234 5678',
            },
        ],
    },
    {
        title: 'Observaciones',
        fields: [
            {
                name: 'observaciones',
                label: 'Notas',
                type: 'textarea',
                span: 2,
            },
        ],
    },
];
