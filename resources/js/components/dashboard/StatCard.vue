<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    hint: { type: String, default: null },
    // default | danger | warning | success | info
    tone: { type: String, default: 'default' },
    href: { type: String, default: null },
    icon: { type: Object, default: null },
});

const toneClasses = computed(() => {
    switch (props.tone) {
        case 'danger':
            return 'text-destructive bg-destructive/10 border-destructive/20 ring-destructive/20';
        case 'warning':
            return 'text-warning bg-warning/10 border-warning/20 ring-warning/20';
        case 'success':
            return 'text-success bg-success/10 border-success/20 ring-success/20';
        case 'info':
            return 'text-info bg-info/10 border-info/20 ring-info/20';
        default:
            return 'text-primary bg-primary/10 border-primary/20 ring-primary/20';
    }
});

const valueColor = computed(() => {
    switch (props.tone) {
        case 'danger': return 'text-destructive';
        case 'warning': return 'text-warning';
        case 'success': return 'text-success';
        case 'info': return 'text-info';
        default: return 'text-foreground';
    }
});
</script>

<template>
    <component :is="href ? Link : 'div'" :href="href ?? undefined" class="group block">
        <Card
            :class="cn(
                'relative overflow-hidden transition-all duration-300 hover:shadow-md active:scale-[0.99]',
                href ? 'hover:border-primary/30 hover:bg-accent/5' : ''
            )"
        >
            <!-- Background Decorative Shape -->
            <div
                class="absolute -right-4 -top-4 size-24 rounded-full opacity-5 transition-transform duration-500 group-hover:scale-110"
                :class="toneClasses.split(' ')[1]"
            />

            <CardHeader class="relative flex-row items-center gap-4 py-4">
                <div
                    v-if="icon"
                    class="flex size-12 items-center justify-center rounded-xl border ring-4 transition-transform duration-300 group-hover:rotate-3"
                    :class="toneClasses"
                >
                    <component :is="icon" class="size-6" />
                </div>
                <div class="flex-1 space-y-1">
                    <CardDescription class="text-xs font-medium uppercase tracking-wider text-muted-foreground/80">
                        {{ label }}
                    </CardDescription>
                    <div class="flex items-baseline gap-2">
                        <CardTitle class="text-2xl font-bold tracking-tight" :class="valueColor">
                            {{ value }}
                        </CardTitle>
                    </div>
                    <p v-if="hint" class="text-xs text-muted-foreground/70">{{ hint }}</p>
                </div>
            </CardHeader>
        </Card>
    </component>
</template>
