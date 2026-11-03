<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { socioSections } from '@/config/roles/socio';
import { store, update } from '@/routes/socios';

const props = defineProps({
    socio: { type: Object, required: true },
    persona: { type: Object, required: true },
});
</script>

<template>
    <RolForm
        :persona="persona"
        :registro="socio"
        rol-label="Socio"
        :sections="socioSections"
        :store-url="store.url()"
        :update-url="update.url(socio.id)"
    />
</template>