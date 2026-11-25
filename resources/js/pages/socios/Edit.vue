<script setup>
import RolForm from '@/components/roles/RolForm.vue';
import { socioSections } from '@/config/roles/socio';
import {baja, reactivar, store, update} from '@/routes/socios';
import CuotasSocio from '@/components/cuotas/CuotasSocio.vue';

const props = defineProps({
    socio: { type: Object, required: true },
    persona: { type: Object, required: true },
    cuotas: { type: Array, default: () => [] },
    medios: { type: Array, default: () => [] },
});
</script>

<template>
    <RolForm
        :persona="persona"
        :registro="socio"
        rol-label="Socio"
        :sections="socioSections"
        :store-url="store.url()"
        :update-url="update.url(socio.id)"
        :baja-url="baja.url(socio.id)"
        :reactivar-url="reactivar.url(socio.id)"
    >
        <template #inferior>
            <CuotasSocio :cuotas="cuotas" :medios="medios" />
        </template>
    </RolForm>
</template>
