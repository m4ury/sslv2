<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { colaboradorSections } from '@/config/roles/colaborador';
import { store } from '@/routes/colaboradores';

defineProps({
    persona: { type: Object, required: true },
});
</script>

<template>
    <RolForm
        :persona="persona"
        rol-label="Colaboradores"
        :sections="colaboradorSections"
        :store-url="store.url()"
    />
</template>
