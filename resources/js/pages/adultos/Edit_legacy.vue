<script setup>
import AdultoForm from '@/components/adultos/AdultoForm.vue';

defineProps({
    adulto: { type: Object, required: true },
    persona: { type: Object, required: true },
});
</script>

<template>
    <AdultoForm :persona="persona" :adulto="adulto" />
</template>