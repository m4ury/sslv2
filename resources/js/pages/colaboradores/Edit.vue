<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { colaboradorSections } from '@/config/roles/colaborador';
import { store, update } from '@/routes/colaboradores';

const props = defineProps({
    colaborador: { type: Object, required: true },
    persona: { type: Object, required: true },
});
</script>

<template>
    <RolForm
        :persona="persona"
        :registro="colaborador"
        rol-label="Colaborador"
        :sections="colaboradorSections"
        :store-url="store.url()"
        :update-url="update.url(colaborador.id)"
    />
</template>
