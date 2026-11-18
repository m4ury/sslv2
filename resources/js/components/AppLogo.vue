<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const name = usePage().props.name;
</script>

<template>
    <div class="flex items-center justify-center">
        <AppLogoIcon class="size-10" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm group-data-[collapsible=icon]:hidden">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{
            name
        }}</span>
    </div>
</template>
