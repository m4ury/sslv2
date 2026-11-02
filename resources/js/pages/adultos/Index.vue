<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    personas: {
        type: Array,
        required: true,
    },
});
</script>
<template>
    <div class="col mx-auto mt-10 p-6 bg-white rounded shadow">
        <Link href="/personas/create" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Crear Persona</Link> <!-- Botón para crear persona -->
    </div>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Listado de Personas</h1>
    </div>
    <div class="table">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">RUT</th>
                    <th class="py-2 px-4 border-b">Nombres</th>
                    <th class="py-2 px-4 border-b">Apellido Paterno</th>
                    <th class="py-2 px-4 border-b">Apellido Materno</th>
                    <th class="py-2 px-4 border-b">Fecha de Nacimiento</th>
                    <th class="py-2 px-4 border-b">Sexo</th>
                    <th class="py-2 px-4 border-b">Email</th>
                    <th class="py-2 px-4 border-b">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="persona in props.personas" :key="persona.id">
                    <td class="py-2 px-4 border-b">{{ persona.nombres }}</td>
                    <td class="py-2 px-4 border-b">{{ persona.rut }}</td>
                    <td class="py-2 px-4 border-b">{{ persona.apellido_paterno }}</td>
                    <td class="py-2 px-4 border-b">{{ persona.apellido_materno }}</td>
                    <td class="py-2 px-4 border-b">{{ persona.fecha_nacimiento }}</td>
                    <td class="py-2 px-4 border-b">{{ persona.sexo }}</td>
                    <td class="py-2 px-4 border-b">{{ persona.email }}</td>
                    <td class="py-2 px-4 border-b">
                        <Link :href="`/personas/${persona.id}/edit`" class="text-blue-500 hover:text-blue-700">Editar</Link>
                        <Link :href="`/personas/${persona.id}`" class="text-green-500 hover:text-green-700 ml-2">Ver</Link>
                        <form :action="`/personas/${persona.id}`" method="POST" class="inline ml-2" @submit.prevent="$inertia.delete($event.target.action)"></form>
                            <input type="hidden" name="_method" value="DELETE">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
