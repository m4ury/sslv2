<script setup>
import { computed } from 'vue';
import { formatCLP } from '@/config/ficha';

const props = defineProps({
    serie: { type: Array, required: true },
});

const maximo = computed(() =>
    Math.max(1, ...props.serie.flatMap((m) => [m.ingresos, m.egresos])),
);

const alto = (valor) => `${Math.round((valor / maximo.value) * 100)}%`;
</script>

<template>
    <div class="flex flex-col gap-3">
        <div class="flex h-40 items-stretch gap-3">
            <div v-for="m in serie" :key="m.periodo" class="flex min-w-0 flex-1 flex-col gap-1">
                <div class="flex min-h-0 flex-1 items-end justify-center gap-1">
                    <div
                        class="w-1/2 max-w-6 rounded-t bg-emerald-600"
                        :style="{ height: alto(m.ingresos) }"
                        :title="`${m.etiqueta} · Ingresos: ${formatCLP(m.ingresos)}`"
                    />
                    <div
                        class="w-1/2 max-w-6 rounded-t bg-destructive/80"
                        :style="{ height: alto(m.egresos) }"
                        :title="`${m.etiqueta} · Egresos: ${formatCLP(m.egresos)}`"
                    />
                </div>
                <span class="text-center text-xs text-muted-foreground">{{ m.etiqueta }}</span>
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs text-muted-foreground">
            <span class="flex items-center gap-1.5">
                <span class="size-2.5 rounded-sm bg-emerald-600" />
                Ingresos
            </span>
            <span class="flex items-center gap-1.5">
                <span class="size-2.5 rounded-sm bg-destructive/80" />
                Egresos
            </span>
            <span class="ml-auto">Pasa el cursor sobre una barra para ver el monto</span>
        </div>
    </div>
</template>
