<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, CalendarPlus } from '@lucide/vue';
import { computed } from 'vue';
import FichaSeccion from '@/components/ficha/FichaSeccion.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { formatCLP, formatDate } from '@/config/ficha';
import { generar as generarCuotas } from '@/routes/cuotas/configuraciones';
import { useConfirm } from '@/composables/useConfirm';


const { confirmar } = useConfirm();

const props = defineProps({
    configuraciones: { type: Array, required: true },
});

const URL_BASE = '/cuotas/configuraciones';

// El select Sí/No trabaja con 1/0
const items = computed(() =>
    props.configuraciones.map((c) => ({ ...c, activa: c.activa ? 1 : 0 })),
);

const seccion = {
    titulo: 'Configuración de cuotas',
    singular: 'configuración',
    descripcion:
        'Define el monto, la vigencia y el día de vencimiento mensual. Cambiar el monto no modifica las cuotas ya generadas.',
    vacio: 'Aún no hay configuraciones de cuota.',
    columns: [
        { key: 'nombre_cuota', label: 'Nombre' },
        { key: 'monto', label: 'Monto', format: (i) => formatCLP(i.monto) },
        { key: 'fecha_inicio', label: 'Desde', type: 'date' },
        { key: 'fecha_termino', label: 'Hasta', format: (i) => (i.fecha_termino ? formatDate(i.fecha_termino) : 'Sin término') },
        { key: 'dia_vencimiento', label: 'Vence', format: (i) => `Día ${i.dia_vencimiento}` },
        {
            key: 'activa',
            label: 'Estado',
            badge: true,
            format: (i) => (i.activa ? 'Activa' : 'Inactiva'),
            variant: (i) => (i.activa ? 'default' : 'outline'),
        },
        { key: 'cuotas_count', label: 'Cuotas' },
    ],
    fields: [
        { name: 'nombre_cuota', label: 'Nombre', required: true, span: 2, placeholder: 'Cuota social 2026' },
        { name: 'monto', label: 'Monto mensual (CLP)', type: 'number', step: 1, min: 1, required: true },
        { name: 'dia_vencimiento', label: 'Día de vencimiento (1 a 28)', type: 'number', step: 1, min: 1, max: 28, required: true },
        { name: 'fecha_inicio', label: 'Fecha de inicio', type: 'date', required: true },
        { name: 'fecha_termino', label: 'Fecha de término', type: 'date' },
        {
            name: 'activa',
            label: 'Activa',
            type: 'select',
            required: true,
            editOnly: true,
            options: [
                { value: 1, label: 'Sí' },
                { value: 0, label: 'No' },
            ],
        },
    ],
};

async function generar(item) {
    const ok = await confirmar({
        titulo: `¿Generar las cuotas de "${item.nombre_cuota}"?`,
        descripcion:
            'Se crearán las cuotas de los socios activos hasta el mes actual. Las que ya existen no se duplican.',
        confirmarTexto: 'Generar cuotas',
    });

    if (ok) {
        router.post(generarCuotas.url(item.id), {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Configuración de cuotas" />

    <div class="mx-auto flex max-w-6xl flex-col gap-6 p-4 md:p-6">
        <div>
            <Button as-child variant="outline">
                <Link href="/cuotas">
                    <ArrowLeft class="size-4" />
                    Volver a cuotas
                </Link>
            </Button>
        </div>

        <FichaSeccion :seccion="seccion" :items="items" :url="URL_BASE">
            <template #acciones="{ item }">
                <button
                    v-if="item.activa"
                    type="button"
                    :class="buttonVariants({ variant: 'ghost', size: 'icon' })"
                    title="Generar cuotas"
                    :aria-label="`Generar cuotas de ${item.nombre_cuota}`"
                    @click.stop="generar(item)"
                >
                    <CalendarPlus class="size-4" />
                </button>
            </template>
        </FichaSeccion>
    </div>
</template>
