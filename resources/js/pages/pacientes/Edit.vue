<script setup>
import { Link } from '@inertiajs/vue3';
import RolForm from '@/components/roles/RolForm.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermisos } from '@/composables/usePermisos';
import { pacienteSections } from '@/config/roles/paciente';
import { store, update } from '@/routes/pacientes';

defineProps({
    paciente: { type: Object, required: true },
    persona: { type: Object, required: true },
});

const { puede } = usePermisos();
</script>

<template>
    <RolForm
        :persona="persona"
        :registro="paciente"
        rol-label="Paciente"
        :sections="pacienteSections"
        :store-url="store.url()"
        :update-url="update.url(paciente.id)"
    >
        <template v-if="puede('ficha.ver')" #lateral>
            <Card>
                <CardHeader>
                    <CardTitle>Ficha clínica</CardTitle>
                    <CardDescription>
                        Controles, prestaciones, patologías, credenciales y
                        documentos de este paciente.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Button as-child>
                        <Link :href="`/pacientes/${paciente.id}/ficha`"
                            >Abrir ficha clínica</Link
                        >
                    </Button>
                </CardContent>
            </Card>
        </template>
    </RolForm>
</template>
