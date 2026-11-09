<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import FichaSeccion from '@/components/ficha/FichaSeccion.vue';

const props = defineProps({
    usuarios: { type: Array, required: true },
    profesiones: { type: Array, required: true },
});

// El select de estado trabaja con 1/0
const items = computed(() =>
    props.usuarios.map((u) => ({ ...u, activo: u.activo ? 1 : 0 })),
);

const seccion = computed(() => ({
    titulo: 'Usuarios',
    singular: 'usuario',
    descripcion: 'Personas con acceso al sistema. Su profesión define lo que pueden hacer.',
    vacio: 'No hay usuarios registrados.',
    columns: [
        { key: 'name', label: 'Nombre' },
        { key: 'email', label: 'Correo' },
        { key: 'profesion', label: 'Profesión', format: (i) => i.profesion?.nombre ?? 'Sin profesión' },
        {
            key: 'activo',
            label: 'Estado',
            badge: true,
            format: (i) => (i.activo ? 'Activo' : 'Inactivo'),
            variant: (i) => (i.activo ? 'default' : 'outline'),
        },
    ],
    fields: [
        { name: 'name', label: 'Nombre', required: true },
        { name: 'email', label: 'Correo', type: 'email', required: true },
        {
            name: 'profesion_id',
            label: 'Profesión',
            type: 'select',
            required: true,
            options: props.profesiones.map((p) => ({ value: p.id, label: p.nombre })),
        },
        {
            name: 'activo',
            label: 'Estado',
            type: 'select',
            required: true,
            editOnly: true,
            options: [
                { value: 1, label: 'Activo' },
                { value: 0, label: 'Inactivo' },
            ],
        },
        { name: 'password', label: 'Contraseña', type: 'password', required: true, createOnly: true },
        { name: 'password_nueva', label: 'Nueva contraseña (opcional)', type: 'password', editOnly: true },
    ],
}));
</script>

<template>
    <Head title="Usuarios" />

    <div class="mx-auto flex max-w-5xl flex-col gap-6 p-4 md:p-6">
        <FichaSeccion
            :seccion="seccion"
            :items="items"
            url="/admin/usuarios"
            sin-eliminar
        />
    </div>
</template>