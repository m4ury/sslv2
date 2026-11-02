<script setup>
import { useForm } from '@inertiajs/vue3';
import { store } from '@/routes/personas';
import InputError from '@/components/InputError.vue';

const form = useForm({
    nombres: '',
    rut: '',
    apellido_paterno: '',
    apellido_materno: '',
    email: '',
    fecha_nacimiento: '',
    sexo: '',
});

function submitForm() {
    form.post(store.url());
}
</script>

<template>
    <div class="mx-auto mt-10 max-w-md rounded bg-white p-6 shadow">
        <h1 class="mb-4 text-2xl font-bold">Crear Persona</h1>
        <div
            v-if="Object.keys(form.errors).length > 0"
            class="mb-4 rounded border border-red-300 bg-red-50 p-4 text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
            role="alert"
            aria-live="assertive"
        >
            <ul>
                <li v-for="(message, field) in form.errors" :key="field">
                    {{ message }}
                </li>
            </ul>
        </div>
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
                <InputError :message="form.errors.rut" />
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
                <InputError :message="form.errors.nombres" />
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
                <InputError :message="form.errors.apellido_paterno" />
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
                <InputError :message="form.errors.apellido_materno" />
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
                <InputError :message="form.errors.email" />
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
                <InputError :message="form.errors.fecha_nacimiento" />
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
                <InputError :message="form.errors.sexo" />
            </div>
            <button
                type="submit"
                class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
            >
                Crear Persona
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
