<script setup>
import AportesColaborador from '@/components/aportes/AportesColaborador.vue';
import RolForm from '@/components/roles/RolForm.vue';
import { colaboradorSections } from '@/config/roles/colaborador';
import { store, update } from '@/routes/colaboradores';

const props = defineProps({
    colaborador: { type: Object, required: true },
    persona: { type: Object, required: true },
    aportes: { type: Array, default: () => [] },
    medios: { type: Array, default: () => [] },
});
</script>

<template>
    <RolForm
        :persona="persona"
        :registro="colaborador"
        rol-label="Colaborador"
        :sections="colaboradorSections"
        :store-url="store.url()"
        :update-url="update.url(colaborador.id)"
    />
    <div class="mx-auto w-full max-w-3xl px-4 pb-6 md:px-6"> <!-- max-w-5xl -->
        <AportesColaborador :colaborador="colaborador" :aportes="aportes" :medios="medios" />
    </div>
</template>
