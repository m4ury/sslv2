<script setup>
import { router } from '@inertiajs/vue3';
import { RotateCcw, UserMinus } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const props = defineProps({
    registro: { type: Object, required: true },
    rolLabel: { type: String, required: true },
    nombre: { type: String, required: true },
    bajaUrl: { type: String, required: true },
    reactivarUrl: { type: String, required: true },
});

const activo = computed(() => props.registro.estado === 'Activo');
const inactivo = computed(() => props.registro.estado === 'Inactivo');

function formatDate(value) {
    if (!value) return null;
    const [y, m, d] = String(value).substring(0, 10).split('-');
    return `${d}-${m}-${y}`;
}

function darDeBaja() {
    if (
        window.confirm(
            `¿Dar de baja a ${props.nombre} como ${props.rolLabel.toLowerCase()}? Quedará inactivo con la fecha de hoy. Sus datos de persona se mantienen.`,
        )
    ) {
        router.patch(props.bajaUrl, {}, { preserveScroll: true });
    }
}

/* function reingresar() {
    if (
        window.confirm(
            `¿Reingresar a ${props.nombre} como ${props.rolLabel.toLowerCase()}? Volverá a estado activo.`,
        )
    ) {
        router.patch(props.reactivarUrl, {}, { preserveScroll: true });
    }
} */
</script>

<template>
    <Card :class="{ 'border-destructive/40': inactivo }">
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                Estado en la agrupación
                <Badge :variant="activo ? 'default' : 'secondary'">
                    {{ registro.estado }}
                </Badge>
            </CardTitle>
            <CardDescription>
                <template v-if="activo">
                    Al dar de baja, pasa a inactivo con la fecha de hoy. La
                    persona se conserva para un posible reingreso.
                </template>
                <template v-else-if="inactivo">
                    Dado de baja
                    <span v-if="registro.fecha_salida">
                        el {{ formatDate(registro.fecha_salida) }}
                    </span>
                    . Puede reingresar cuando corresponda.
                </template>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <Button v-if="activo" variant="destructive" @click="darDeBaja">
                <UserMinus class="size-4" />
                Dar de baja
            </Button>
            <Button v-else-if="inactivo" @click="reingresar">
                <RotateCcw class="size-4" />
                Reingresar
            </Button>
        </CardContent>
    </Card>
</template>