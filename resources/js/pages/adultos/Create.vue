<script setup>
import { useForm } from '@inertiajs/vue3';
import { store } from '@/routes/adultos';

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
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Crear Adulto Mayor</h1>
        <form @submit.prevent="submitForm">

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Guardar</button>
            <button type="button" @click="$inertia.visit('/personas')" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 ml-2">Volver</button>
        </form>
    </div>
</template>
