<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { cuidadorSections } from '@/config/roles/cuidador';
import { store } from '@/routes/cuidadores';

defineProps({
    persona: { type: Object, required: true },
});
</script>

<template>
    <RolForm
        :persona="persona"
        rol-label="Cuidador"
        :sections="cuidadorSections"
        :store-url="store.url()"
    />
</template>
