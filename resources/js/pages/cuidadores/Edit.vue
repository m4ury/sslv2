<script setup>
import { Link } from '@inertiajs/vue3';
import RolForm from '@/components/roles/RolForm.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDate } from '@/config/ficha';
import { cuidadorSections } from '@/config/roles/cuidador';
import { store, update } from '@/routes/cuidadores';

defineProps({
    cuidador: { type: Object, required: true },
    persona: { type: Object, required: true },
    asignaciones: { type: Array, default: () => [] },
});
</script>

<template>
    <RolForm
        :persona="persona"
        :registro="cuidador"
        rol-label="Cuidador"
        :sections="cuidadorSections"
        :store-url="store.url()"
        :update-url="update.url(cuidador.id)"
    />

    <div class="mx-auto w-full max-w-3xl px-4 pb-6 md:px-6">
        <Card>
            <CardHeader>
                <CardTitle>Pacientes a su cargo</CardTitle>
                <CardDescription>
                    Las asignaciones se gestionan desde la ficha de cada socio,
                    en la pestaña Cuidadores.
                </CardDescription>
            </CardHeader>
            <CardContent class="flex flex-col gap-3">
                <div
                    v-for="a in asignaciones"
                    :key="a.id"
                    class="flex flex-col gap-2 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex flex-col gap-1">
                        <Link
                            :href="`/socios/${a.socio_id}/ficha`"
                            class="text-sm font-medium underline-offset-4 hover:underline"
                        >
                            {{ a.socio?.persona?.full_name }}
                        </Link>
                        <span class="text-xs text-muted-foreground">
                            {{ a.vinculo || 'Sin vínculo indicado' }} · desde
                            {{ formatDate(a.fecha_inicio) }}
                            <template v-if="a.fecha_fin">
                                hasta {{ formatDate(a.fecha_fin) }}
                            </template>
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <Badge v-if="a.es_principal">Principal</Badge>
                        <Badge variant="outline">
                            {{ a.fecha_fin ? 'Finalizada' : 'Vigente' }}
                        </Badge>
                    </div>
                </div>

                <p
                    v-if="asignaciones.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    Aún no tiene pacientes asignados.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
