<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { adultoSections } from '@/config/roles/adulto';
import { store, update } from '@/routes/adultos';

const props = defineProps({
    adulto: { type: Object, required: true },
    persona: { type: Object, required: true },
});
</script>

<template>
    <RolForm
        :persona="persona"
        :registro="adulto"
        rol-label="Adulto"
        :sections="adultoSections"
        :store-url="store.url()"
        :update-url="update.url(adulto.id)"
        :baja-url="baja.url(adulto.id)"
        :reactivar-url="reactivar.url(adulto.id)"
    />
</template>