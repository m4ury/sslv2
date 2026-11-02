<script setup>
import { useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/PersonaController';

const props = defineProps({
    persona: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    nombres: props.persona.nombres,
    rut: props.persona.rut,
    apellido_paterno: props.persona.apellido_paterno,
    apellido_materno: props.persona.apellido_materno,
    email: props.persona.email,
    fecha_nacimiento: props.persona.fecha_nacimiento ?? '',
    sexo: props.persona.sexo ?? '',
});

function submitForm() {
    form.patch(update.url(props.persona.id));
}
</script>
<template>
    <h1 class="mb-4 text-2xl font-bold">Editar Persona</h1>
    <div class="col mx-auto mt-10 rounded bg-white p-6 shadow">
        <form @submit.prevent="submitForm">
            <div class="mb-4">
                <label for="rut" class="block text-gray-700">RUT:</label>
                <input
                    type="text"
                    id="rut"
                    v-model="form.rut"
                    class="w-full rounded border px-3 py-2"
                    required
                />
            </div>
            <div class="mb-4">
                <label for="nombres" class="block text-gray-700"
                    >Nombres:</label
                >
                <input
                    type="text"
                    id="nombres"
                    v-model="form.nombres"
                    class="w-full rounded border px-3 py-2"
                    required
                />
            </div>
            <div class="mb-4">
                <label for="apellido_paterno" class="block text-gray-700"
                    >Apellido Paterno:</label
                >
                <input
                    type="text"
                    id="apellido_paterno"
                    v-model="form.apellido_paterno"
                    class="w-full rounded border px-3 py-2"
                    required
                />
            </div>
            <div class="mb-4">
                <label for="apellido_materno" class="block text-gray-700"
                    >Apellido Materno:</label
                >
                <input
                    type="text"
                    id="apellido_materno"
                    v-model="form.apellido_materno"
                    class="w-full rounded border px-3 py-2"
                    required
                />
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email:</label>
                <input
                    type="email"
                    id="email"
                    v-model="form.email"
                    class="w-full rounded border px-3 py-2"
                    required
                />
            </div>
            <div class="mb-4">
                <label for="fecha_nacimiento" class="block text-gray-700"
                    >Fecha de Nacimiento:</label
                >
                <input
                    type="date"
                    id="fecha_nacimiento"
                    v-model="form.fecha_nacimiento"
                    class="w-full rounded border px-3 py-2"
                    required
                />
            </div>
            <div class="mb-4">
                <label for="sexo" class="block text-gray-700">Sexo:</label>
                <select
                    id="sexo"
                    v-model="form.sexo"
                    class="w-full rounded border px-3 py-2"
                    required
                >
                    <option value="">Seleccione...</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                </select>
            </div>
            <button
                type="submit"
                class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
            >
                Actualizar Persona
            </button>
            <button
                type="button"
                @click="$inertia.visit('/personas')"
                class="ml-2 rounded bg-gray-500 px-4 py-2 text-white hover:bg-gray-600"
            >
                Volver
            </button>
        </form>
    </div>
</template>
