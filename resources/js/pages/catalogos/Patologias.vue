<script setup>
import { Head } from '@inertiajs/vue3';
import FichaSeccion from '@/components/ficha/FichaSeccion.vue';

defineProps({
    patologias: { type: Array, required: true },
});

const seccion = {
    titulo: 'Catálogo de patologías',
    singular: 'patología',
    descripcion:
        'Lista compartida. Al renombrar una patología, el cambio se refleja en todos los adultos que la tienen.',
    vacio: 'El catálogo está vacío.',
    columns: [
        { key: 'nombre', label: 'Patología' },
        { key: 'codigo_cie10', label: 'CIE-10' },
        { key: 'registros_count', label: 'Registros asociados' },
    ],
    fields: [
        { name: 'nombre', label: 'Nombre', required: true, span: 2 },
        { name: 'codigo_cie10', label: 'Código CIE-10' },
    ],
};
</script>

<template>
    <Head title="Catálogo de patologías" />

    <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6">
        <FichaSeccion
            :seccion="seccion"
            :items="patologias"
            url="/catalogos/patologias"
        />
    </div>
</template>