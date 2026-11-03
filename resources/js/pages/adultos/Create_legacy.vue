<script setup>
import AdultoForm from '@/components/adultos/AdultoForm.vue';

defineProps({
    persona: { type: Object, required: true },
});
</script>

<template>
    <AdultoForm :persona="persona" />
</template>