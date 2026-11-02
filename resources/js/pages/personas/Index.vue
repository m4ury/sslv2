<script setup>
import { Link, router } from '@inertiajs/vue3';
import { destroy } from '@/actions/App/Http/Controllers/PersonaController';
import UserInfo from '@/components/UserInfo.vue';

const props = defineProps({
    personas: {
        type: Array,
        required: true,
    },
});

function deletePersona(personaId) {
    if (window.confirm('¿Estás seguro de que deseas eliminar esta persona?')) {
        router.delete(destroy.url(personaId));
    }
}
</script>

<template>
    <div class="col mx-auto mt-10 rounded bg-white p-6 shadow">
        <Link
            href="/personas/create"
            class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
            >Crear Persona</Link
        >
        <!-- Botón para crear persona -->
    </div>
    <div class="mx-auto mt-10 max-w-md rounded bg-white p-6 shadow">
        <h1 class="mb-4 text-2xl font-bold">Listado de Personas</h1>
    </div>
    <div class="table">
        <table class="table-responsive striped min-w-full bg-white">
            <thead>
                <tr>
                    <th class="border-b px-4 py-2">RUT</th>
                    <th class="border-b px-4 py-2">Nombre completo</th>
                    <th class="border-b px-4 py-2">Fecha de Nacimiento</th>
                    <th class="border-b px-4 py-2">Edad</th>
                    <th class="border-b px-4 py-2">Sexo</th>
                    <th class="border-b px-4 py-2">Email</th>
                    <th class="border-b px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="persona in props.personas" :key="persona.id">
                    <td class="border-b px-4 py-2">{{ persona.rut }}</td>
                    <td class="border-b px-4 py-2">{{ persona.full_name }}</td>
                    <td class="border-b px-4 py-2">
                        {{ persona.fecha_nacimiento }}
                    </td>
                    <td class="border-b px-4 py-2">{{ persona.edad }}</td>
                    <td class="border-b px-4 py-2">{{ persona.sexo }}</td>
                    <td class="border-b px-4 py-2">{{ persona.email }}</td>
                    <td class="border-b px-4 py-2">
                        <Link
                            :href="`/personas/${persona.id}/edit`"
                            class="text-blue-500 hover:text-blue-700"
                            >Editar</Link
                        >
                        <Link
                            :href="`/personas/${persona.id}`"
                            class="ml-2 text-green-500 hover:text-green-700"
                            >Ver</Link
                        >
                        <button
                            type="button"
                            class="ml-2 text-red-500 hover:text-red-700"
                            @click="deletePersona(persona.id)"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
