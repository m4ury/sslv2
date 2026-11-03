<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
        <template v-for="(link, i) in links" :key="i">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="rounded-md border px-3 py-1.5 text-sm transition-colors"
                :class="
                    link.active
                        ? 'border-primary bg-primary text-primary-foreground'
                        : 'bg-background hover:bg-accent'
                "
            >
                <span v-html="link.label" />
            </Link>
            <span
                v-else
                class="rounded-md border px-3 py-1.5 text-sm text-muted-foreground opacity-50"
                v-html="link.label"
            />
        </template>
    </nav>
</template>