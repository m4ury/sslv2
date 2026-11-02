<script setup>


</script>


<template>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Detalles de la Persona</h1>
        <div class="mb-4">
            <p><strong>RUT:</strong> {{ $page.props.persona.rut }}</p>
            <p><strong>Nombres:</strong> {{ $page.props.persona.nombres }}</p>
            <p><strong>Apellido Paterno:</strong> {{ $page.props.persona.apellido_paterno }}</p>
            <p><strong>Apellido Materno:</strong> {{ $page.props.persona.apellido_materno }}</p>
            <p><strong>Email:</strong> {{ $page.props.persona.email }}</p>
            <p><strong>Fecha de Nacimiento:</strong> {{ $page.props.persona.fecha_nacimiento }}</p>
            <p><strong>Sexo:</strong> {{ $page.props.persona.sexo }}</p>
        </div>
        <button type="button" @click="$inertia.visit(`/adultos/create`)" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Pasar a Adulto</button>
        <button type="button" @click="$inertia.visit(`/socios/create`)" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Pasar a Socio</button>
        <button type="button" @click="$inertia.visit(`/adultos/colaborador`)" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Pasar a Adulto Colaborador</button>
        <form @submit.prevent="$inertia.delete(`/personas/${$page.props.persona.id}`)" class="inline">
            <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">Eliminar Persona</button>
        </form>
        <button type="button" @click="$inertia.visit(`/personas/${$page.props.persona.id}/edit`)" class="bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600">Editar Persona</button>
        <button type="button" @click="$inertia.visit('/personas')" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">Volver</button>
    </div>
</template>
