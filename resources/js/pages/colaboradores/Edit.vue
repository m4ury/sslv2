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
    >
        <template #lateral>
            <AportesColaborador
                :colaborador="colaborador"
                :aportes="aportes"
                :medios="medios"
            />
        </template>
    </RolForm>
</template>
