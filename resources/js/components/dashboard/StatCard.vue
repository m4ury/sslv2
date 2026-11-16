<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    hint: { type: String, default: null },
    // default | danger | warning
    tone: { type: String, default: 'default' },
    href: { type: String, default: null },
});

const colorValor = computed(
    () =>
        ({
            danger: 'text-destructive',
            warning: 'text-amber-600 dark:text-amber-400',
        })[props.tone] ?? '',
);
</script>

<template>
    <component :is="href ? Link : 'div'" :href="href ?? undefined" class="block">
        <Card :class="{ 'transition-colors hover:bg-accent/40': href }">
            <CardHeader>
                <CardDescription>{{ label }}</CardDescription>
                <CardTitle class="text-2xl" :class="colorValor">{{ value }}</CardTitle>
                <p v-if="hint" class="text-xs text-muted-foreground">{{ hint }}</p>
            </CardHeader>
        </Card>
    </component>
</template>
