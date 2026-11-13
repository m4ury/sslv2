<script setup>
import { Input } from '@/components/ui/input';

defineProps({
    filtros: { type: Object, required: true },
    categorias: { type: Array, default: () => [] },
    etiquetaCategoria: { type: String, default: 'Todas las categorías' },
    placeholder: { type: String, default: 'Buscar...' },
});

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
</script>

<template>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <Input
            v-model="filtros.search"
            type="search"
            :placeholder="placeholder"
            class="lg:max-w-sm lg:flex-1"
        />

        <select v-model="filtros.categoria" :class="selectClass">
            <option value="">{{ etiquetaCategoria }}</option>
            <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
        </select>

        <select v-model="filtros.estado" :class="selectClass">
            <option value="">Vigentes y anulados</option>
            <option value="Vigente">Solo vigentes</option>
            <option value="Anulado">Solo anulados</option>
        </select>

        <div class="flex items-center gap-2">
            <Input v-model="filtros.desde" type="date" aria-label="Desde" />
            <span class="text-sm text-muted-foreground">a</span>
            <Input v-model="filtros.hasta" type="date" aria-label="Hasta" />
        </div>
    </div>
</template>