export function formatDate(value) {
    if (!value) return '—';
    const [y, m, d] = String(value).substring(0, 10).split('-');
    return `${d}-${m}-${y}`;
}

// Fecha local en formato YYYY-MM-DD (evita el desfase de toISOString)
export function fechaLocal(offsetDias = 0) {
    const d = new Date();
    d.setDate(d.getDate() + offsetDias);
    return d.toLocaleDateString('en-CA');
}

export function estadoVigencia(fecha) {
    if (!fecha) return null;
    const f = String(fecha).substring(0, 10);
    if (f < fechaLocal()) return 'Vencida';
    if (f <= fechaLocal(30)) return 'Por vencer';
    return 'Vigente';
}

function formatTamano(bytes) {
    if (!bytes) return '—';
    return bytes < 1024 * 1024
        ? `${Math.round(bytes / 1024)} KB`
        : `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

function resumenMediciones(control, catalogo) {
    const m = Object.fromEntries(
        (control.mediciones ?? []).map((x) => [x.tipo, x.valor]),
    );
    const partes = [];

    if (m.pa_sistolica != null || m.pa_diastolica != null) {
        partes.push(`PA ${m.pa_sistolica ?? '?'}/${m.pa_diastolica ?? '?'}`);
    }

    for (const [tipo, def] of Object.entries(catalogo)) {
        if (tipo.startsWith('pa_') || m[tipo] == null) continue;
        partes.push(`${def.label} ${m[tipo]} ${def.unidad}`);
    }

    return partes.join(' · ') || '—';
}

const VARIANTES = {
    Activa: 'default',
    Controlada: 'secondary',
    Resuelta: 'outline',
    Pendiente: 'default',
    Realizado: 'secondary',
    Cancelado: 'outline',
    'No asistió': 'destructive',
    Vencida: 'destructive',
    'Por vencer': 'default',
    Vigente: 'secondary',
};

const variante = (valor) => VARIANTES[valor] ?? 'secondary';

export function crearSecciones({ catalogos, controles, catalogoPatologias = [], catalogoCuidadores = [], }) {
    const etiquetaControl = (c) => `${formatDate(c.fecha)} · ${c.tipo}`;

    const camposMediciones = Object.entries(catalogos.mediciones).map(
        ([tipo, def]) => ({
            name: `med_${tipo}`,
            label: `${def.label} (${def.unidad})`,
            type: 'number',
            step: 'any',
            min: def.min,
            max: def.max,
            group: 'Mediciones',
        }),
    );

    return {
        patologias: {
            titulo: 'Patologías',
            singular: 'patología',
            descripcion: 'Diagnósticos del adulto, tomados del catálogo de patologías.',
            vacio: 'Sin patologías registradas.',
            columns: [
                { key: 'patologia', label: 'Patología', format: (i) => i.patologia?.nombre ?? '—' },
                /* { key: 'codigo', label: 'CIE-10', format: (i) => i.patologia?.codigo_cie10 ?? '—' }, */
                { key: 'fecha_diagnostico', label: 'Diagnóstico', type: 'date' },
                { key: 'estado', label: 'Estado', badge: true, variant: (i) => variante(i.estado) },
                { key: 'observaciones', label: 'Observaciones', class: 'max-w-xs truncate' },
            ],
            fields: [
                {
                    name: 'patologia_id',
                    label: 'Patología',
                    type: 'select',
                    required: true,
                    span: 2,
                    options: catalogoPatologias.map((p) => ({
                        value: p.id,
                        /* label: p.codigo_cie10 ? `${p.nombre} (${p.codigo_cie10})` : p.nombre, */
                        label:`${p.nombre}`
                    })),
                },
                { name: 'fecha_diagnostico', label: 'Fecha de diagnóstico', type: 'date' },
                { name: 'estado', label: 'Estado', type: 'select', required: true, options: catalogos.estados_patologia },
                { name: 'observaciones', label: 'Observaciones', type: 'textarea', span: 2 },
            ],
        },

        controles: {
            titulo: 'Controles',
            singular: 'control',
            descripcion: 'Controles realizados y sus mediciones.',
            vacio: 'Sin controles registrados.',
            columns: [
                { key: 'fecha', label: 'Fecha', type: 'date' },
                { key: 'tipo', label: 'Tipo' },
                { key: 'mediciones', label: 'Mediciones', format: (i) => resumenMediciones(i, catalogos.mediciones) },
                { key: 'autor', label: 'Registrado por', format: (i) => i.autor?.name ?? '—' },
                { key: 'observaciones', label: 'Observaciones', class: 'max-w-xs truncate' },
            ],
            fields: [
                { name: 'fecha', label: 'Fecha', type: 'date', required: true },
                { name: 'tipo', label: 'Tipo de control', type: 'select', required: true, options: catalogos.tipos_control },
                { name: 'observaciones', label: 'Observaciones', type: 'textarea', span: 2 },
                ...camposMediciones,
            ],
        },

        prestaciones: {
            titulo: 'Prestaciones',
            singular: 'prestación',
            descripcion: 'Procedimientos y atenciones entregadas.',
            vacio: 'Sin prestaciones registradas.',
            columns: [
                { key: 'fecha', label: 'Fecha', type: 'date' },
                { key: 'nombre', label: 'Prestación' },
                {
                    key: 'control_id',
                    label: 'Control asociado',
                    format: (i) => {
                        const c = controles.find((x) => x.id === i.control_id);
                        return c ? etiquetaControl(c) : '—';
                    },
                },
                { key: 'detalle', label: 'Detalle', class: 'max-w-xs truncate' },
            ],
            fields: [
                { name: 'fecha', label: 'Fecha', type: 'date', required: true },
                { name: 'nombre', label: 'Prestación', required: true },
                {
                    name: 'control_id',
                    label: 'Control asociado',
                    type: 'select',
                    required: true,
                    span: 2,
                    options: controles.map((c) => ({ value: c.id, label: etiquetaControl(c) })),
                },
                { name: 'detalle', label: 'Detalle', type: 'textarea', span: 2 },
            ],
        },

        programados: {
            titulo: 'Controles programados',
            singular: 'control programado',
            descripcion: 'Próximos controles agendados.',
            vacio: 'Sin controles programados.',
            columns: [
                { key: 'fecha_programada', label: 'Fecha', type: 'date' },
                { key: 'tipo', label: 'Tipo' },
                { key: 'estado', label: 'Estado', badge: true, variant: (i) => variante(i.estado) },
                { key: 'observaciones', label: 'Observaciones', class: 'max-w-xs truncate' },
            ],
            fields: [
                { name: 'fecha_programada', label: 'Fecha programada', type: 'date', required: true },
                { name: 'tipo', label: 'Tipo de control', type: 'select', required: true, options: catalogos.tipos_control },
                { name: 'estado', label: 'Estado', type: 'select', required: true, editOnly: true, options: catalogos.estados_programado },
                { name: 'observaciones', label: 'Observaciones', type: 'textarea', span: 2 },
            ],
        },

        credenciales: {
            titulo: 'Credenciales',
            singular: 'credencial',
            descripcion: 'Credenciales y su vigencia.',
            vacio: 'Sin credenciales registradas.',
            columns: [
                { key: 'tipo_discapacidad', label: 'Tipo' },
                { key: 'numero', label: 'N°' },
                { key: 'organismo_emisor', label: 'Emisor' },
                { key: 'fecha_expiracion', label: 'Vence', type: 'date' },
                { key: 'porcentaje_discapacidad', label: '% Discap.', type: 'number'},
                {
                    key: 'vigencia',
                    label: 'Vigencia',
                    badge: true,
                    format: (i) => estadoVigencia(i.fecha_expiracion) ?? 'Sin vencimiento',
                    variant: (i) => variante(estadoVigencia(i.fecha_expiracion)),
                },
            ],
            fields: [
                { name: 'tipo_discapacidad', label: 'Tipo', type: 'select', required: true, options: catalogos.tipo_discapacidad },
                { name: 'porcentaje_discapacidad', label: '% Discap.', type: 'number', step: 'any' },
                { name: 'numero', label: 'Número' },
                { name: 'organismo_emisor', label: 'Emisor' },
                { name: 'fecha_emision', label: 'Fecha de emisión', type: 'date' },
                { name: 'fecha_expiracion', label: 'Fecha de vencimiento', type: 'date' },
                { name: 'observaciones', label: 'Observaciones', type: 'textarea', span: 2 },
            ],
        },

        documentos: {
            titulo: 'Documentos',
            singular: 'documento',
            descripcion: 'Exámenes, informes y otros archivos (PDF, imágenes, Word; máx. 10 MB).',
            vacio: 'Sin documentos cargados.',
            columns: [
                { key: 'nombre', label: 'Nombre' },
                { key: 'tipo', label: 'Tipo' },
                { key: 'fecha_documento', label: 'Fecha', type: 'date' },
                { key: 'tamano', label: 'Tamaño', format: (i) => formatTamano(i.tamano) },
            ],
            fields: [
                { name: 'archivo', label: 'Archivo', type: 'file', required: true, createOnly: true, span: 2 },
                { name: 'nombre', label: 'Nombre del documento', required: true },
                { name: 'tipo', label: 'Tipo', type: 'select', required: true, options: catalogos.tipos_documento },
                { name: 'fecha_documento', label: 'Fecha del documento', type: 'date' },
            ],
        },

        cuidadores: {
            titulo: 'Cuidadores',
            singular: 'cuidador',
            descripcion: 'Personas que cuidan a este adulto y desde cuándo.',
            vacio: 'Sin cuidadores asignados.',
            columns: [
                { key: 'cuidador', label: 'Cuidador', format: (i) => i.cuidador?.persona?.full_name ?? '—' },
                { key: 'telefono', label: 'Teléfono', format: (i) => i.cuidador?.telefono ?? '—' },
                { key: 'vinculo', label: 'Vínculo' },
                {
                    key: 'es_principal',
                    label: 'Rol',
                    badge: true,
                    format: (i) => (i.es_principal ? 'Principal' : 'Secundario'),
                    variant: (i) => (i.es_principal ? 'default' : 'outline'),
                },
                { key: 'fecha_inicio', label: 'Desde', type: 'date' },
                { key: 'fecha_fin', label: 'Hasta', type: 'date' },
            ],
            fields: [
                {
                    name: 'cuidador_id',
                    label: 'Cuidador',
                    type: 'select',
                    required: true,
                    span: 2,
                    options: catalogoCuidadores.map((c) => ({
                        value: c.id,
                        label: `${c.nombre} · ${c.rut}${c.estado !== 'Activo' ? ' (inactivo)' : ''}`,
                    })),
                },
                { name: 'vinculo', label: 'Vínculo', placeholder: 'Hija, vecino, cuidador contratado...' },
                {
                    name: 'es_principal',
                    label: 'Cuidador principal',
                    type: 'select',
                    required: true,
                    options: [
                        { value: 1, label: 'Sí' },
                        { value: 0, label: 'No' },
                    ],
                },
                { name: 'fecha_inicio', label: 'Fecha de inicio', type: 'date', required: true },
                { name: 'fecha_fin', label: 'Fecha de término', type: 'date', editOnly: true },
                { name: 'observaciones', label: 'Observaciones', type: 'textarea', span: 2 },
            ],
        },

    };
}
export function formatCLP(value) {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        maximumFractionDigits: 0,
    }).format(value ?? 0);
}

// "2026-03" → "Marzo 2026"
export function formatPeriodo(periodo) {
    if (!periodo) return '—';
    const [y, m] = String(periodo).split('-');
    const mes = new Date(Number(y), Number(m) - 1, 1).toLocaleDateString('es-CL', {
        month: 'long',
    });
    return `${mes.charAt(0).toUpperCase()}${mes.slice(1)} ${y}`;
}
