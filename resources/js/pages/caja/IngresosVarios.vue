<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { computed } from 'vue';
import FiltrosLibro from '@/components/caja/FiltrosLibro.vue';
import MovimientosLibro from '@/components/caja/MovimientosLibro.vue';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useFiltros } from '@/composables/useFiltros';
import { usePermisos } from '@/composables/usePermisos';
import { formatCLP } from '@/config/ficha';

const props = defineProps({
    ingresos: { type: Object, required: true },
    total: { type: Number, default: 0 },
    filters: { type: Object, default: () => ({}) },
    categorias: { type: Array, default: () => [] },
    tiposDocumento: { type: Array, default: () => [] },
    medios: { type: Array, default: () => [] },
});

const { puede } = usePermisos();

const filtros = useFiltros('/caja/ingresos', {
    search: props.filters.search ?? '',
    categoria: props.filters.categoria ?? '',
    estado: props.filters.estado ?? '',
    desde: props.filters.desde ?? '',
    hasta: props.filters.hasta ?? '',
});

const config = computed(() => ({
    titulo: 'Otros ingresos',
    descripcion: 'Subvenciones, donaciones y otros ingresos. Suman al flujo de caja.',
    singular: 'ingreso',
    columns: [
        { key: 'fecha', label: 'Fecha', type: 'date' },
        { key: 'categoria', label: 'Categoría' },
        {
            key: 'descripcion',
            label: 'Descripción',
            format: (i) => (i.origen ? `${i.descripcion} · ${i.origen}` : i.descripcion),
        },
        {
            key: 'documento',
            label: 'Documento',
            format: (i) => `${i.tipo_documento}${i.num_documento ? ` N° ${i.num_documento}` : ''}`,
        },
        { key: 'monto', label: 'Monto', type: 'money' },
    ],
    fields: [
        { name: 'fecha', label: 'Fecha', type: 'date', required: true, default: 'hoy', maxHoy: true },
        { name: 'monto', label: 'Monto', type: 'number', required: true, min: 1, step: 1 },
        { name: 'categoria', label: 'Categoría', type: 'select', required: true, options: props.categorias },
        { name: 'origen', label: 'Origen de los fondos', placeholder: 'Municipalidad de ...' },
        { name: 'descripcion', label: 'Descripción', required: true, span: 2 },
        {
            name: 'tipo_documento',
            label: 'Documento',
            type: 'select',
            required: true,
            options: props.tiposDocumento,
        },
        {
            name: 'num_documento',
            label: 'N° de documento',
            required: (f) => !!f.tipo_documento && f.tipo_documento !== 'Sin documento',
        },
        {
            name: 'medio_pago',
            label: 'Medio de pago',
            type: 'select',
            required: true,
            default: 'Efectivo',
            options: props.medios,
        },
    ],
}));
</script>

<template>
    <Head title="Otros ingresos" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Otros ingresos</h1>
                <p class="text-sm text-muted-foreground">Ingresos del flujo de caja distintos de las cuotas.</p>
            </div>
            <Button as-child variant="outline">
                <Link href="/caja">
                    <ArrowLeft class="size-4" />
                    Volver a caja
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardDescription>Total vigente (según filtros)</CardDescription>
                <CardTitle class="text-2xl">{{ formatCLP(total) }}</CardTitle>
            </CardHeader>
        </Card>

        <MovimientosLibro
            :config="config"
            :items="ingresos"
            url="/caja/ingresos"
            :puede-registrar="puede('caja.registrar')"
        >
            <template #filtros>
                <FiltrosLibro
                    :filtros="filtros"
                    :categorias="categorias"
                    placeholder="Buscar por descripción, origen o documento..."
                />
            </template>
        </MovimientosLibro>
    </div>
</template>