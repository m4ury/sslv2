<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { adultoSections } from '@/config/roles/adulto';
import { store } from '@/routes/adultos';

defineProps({
    persona: { type: Object, required: true },
});
</script>

<template>
    <RolForm
        :persona="persona"
        rol-label="Adulto"
        :sections="adultoSections"
        :store-url="store.url()"
    />
</template>