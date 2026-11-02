<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    persona: {
        type: Object,
        required: true,
    },
});

const { patch } = useForm({
    nombres: props.persona.nombres,
    rut: props.persona.rut,
    apellido_paterno: props.persona.apellido_paterno,
    apellido_materno: props.persona.apellido_materno,
    email: props.persona.email,
    fecha_nacimiento: props.persona.fecha_nacimiento,
    sexo: props.persona.sexo,
});

</script>
<template>
    <h1 class="text-2xl font-bold mb-4">Editar Persona</h1>
    <div class="col mx-auto mt-10 p-6 bg-white rounded shadow">

    <form @submit.prevent="patch(`/personas/${props.persona.id}`)">
        <div class="mb-4">
            <label for="rut" class="block text-gray-700">RUT:</label>
            <input type="text" id="rut" v-model="props.persona.rut" class="border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="nombres" class="block text-gray-700">Nombres:</label>
            <input type="text" id="nombres" v-model="props.persona.nombres" class="border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="apellido_paterno" class="block text-gray-700">Apellido Paterno:</label>
            <input type="text" id="apellido_paterno" v-model="props.persona.apellido_paterno" class="border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="apellido_materno" class="block text-gray-700">Apellido Materno:</label>
            <input type="text" id="apellido_materno" v-model="props.persona.apellido_materno" class="border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700">Email:</label>
            <input type="email" id="email" v-model="props.persona.email" class="border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="fecha_nacimiento" class="block text-gray-700">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" v-model="props.persona.fecha_nacimiento" class="border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="sexo" class="block text-gray-700">Sexo:</label>
            <select id="sexo" v-model="props.persona.sexo" class="border rounded w-full py-2 px-3" required>
                <option value="">Seleccione...</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Actualizar Persona</button>
        <button type="button" @click="$inertia.visit('/personas')" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 ml-2">Volver</button>
    </form>
    </div>
</template>
