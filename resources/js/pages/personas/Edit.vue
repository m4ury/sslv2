<script setup>
import PersonaForm from '@/components/personas/PersonaForm.vue';

defineProps({
    persona: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <PersonaForm :persona="persona" />
</template>