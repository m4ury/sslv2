<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { pacienteSections } from '@/config/roles/paciente';
import { store } from '@/routes/pacientes';

defineProps({
    persona: { type: Object, required: true },
});
</script>

<template>
    <RolForm
        :persona="persona"
        rol-label="Paciente"
        :sections="pacienteSections"
        :store-url="store.url()"
    />
</template>
