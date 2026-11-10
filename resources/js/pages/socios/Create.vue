<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { socioSections } from '@/config/roles/socio_old';
import { store } from '@/routes/socios';

defineProps({
    persona: { type: Object, required: true },
});
</script>

<template>
    <RolForm
        :persona="persona"
        rol-label="Socio"
        :sections="socioSections"
        :store-url="store.url()"
    />
</template>
