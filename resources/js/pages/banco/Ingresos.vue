<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { FileText } from '@lucide/vue';
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
    conceptos: { type: Array, default: () => [] },
});

const { puede } = usePermisos();

const filtros = useFiltros('/banco', {
    search: props.filters.search ?? '',
    categoria: props.filters.categoria ?? '',
    estado: props.filters.estado ?? '',
    desde: props.filters.desde ?? '',
    hasta: props.filters.hasta ?? '',
});

const config = computed(() => ({
    titulo: 'Ingresos bancarios',
    descripcion: 'Abonos en la cuenta bancaria. Libro aparte: no suma al flujo de caja.',
    singular: 'ingreso bancario',
    columns: [
        { key: 'fecha', label: 'Fecha', type: 'date' },
        { key: 'concepto', label: 'Concepto' },
        { key: 'descripcion', label: 'Descripción' },
        { key: 'banco', label: 'Banco' },
        { key: 'num_operacion', label: 'N° operación' },
        { key: 'monto', label: 'Monto', type: 'money' },
    ],
    fields: [
        { name: 'fecha', label: 'Fecha', type: 'date', required: true, default: 'hoy', maxHoy: true },
        { name: 'monto', label: 'Monto', type: 'number', required: true, min: 1, step: 1 },
        { name: 'concepto', label: 'Concepto', type: 'select', required: true, options: props.conceptos },
        { name: 'banco', label: 'Banco' },
        { name: 'descripcion', label: 'Descripción', required: true, span: 2 },
        { name: 'num_operacion', label: 'N° de operación' },
    ],
}));
</script>

<template>
    <Head title="Ingresos bancarios" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Ingresos bancarios</h1>
                <p class="text-sm text-muted-foreground">Registro reservado, separado del flujo de caja.</p>
            </div>
            <Button as-child variant="outline">
                <Link href="/banco/informe">
                    <FileText class="size-4" />
                    Informe mensual
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
            url="/banco/ingresos"
            :puede-registrar="puede('banco.registrar')"
        >
            <template #filtros>
                <FiltrosLibro
                    :filtros="filtros"
                    :categorias="conceptos"
                    etiqueta-categoria="Todos los conceptos"
                    placeholder="Buscar por descripción, banco u operación..."
                />
            </template>
        </MovimientosLibro>
    </div>
</template>