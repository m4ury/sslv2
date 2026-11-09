<script setup>
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { formatCLP, formatDate, formatPeriodo } from '@/config/ficha';

defineProps({
    cuota: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const anulando = ref(null);
const form = useForm({ motivo: '' });

function iniciarAnulacion(pago) {
    anulando.value = pago.id;
    form.reset();
    form.clearErrors();
}

function confirmarAnulacion(pago) {
    form.post(`/pagos/${pago.id}/anular`, {
        preserveScroll: true,
        onSuccess: () => {
            anulando.value = null;
        },
    });
}
</script>

<template>
    <Dialog :open="!!cuota" @update:open="(abierto) => !abierto && emit('close')">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Pagos de la cuota</DialogTitle>
                <DialogDescription>
                    {{ cuota?.socio?.persona?.full_name }} ·
                    {{ formatPeriodo(cuota?.periodo) }} · Pagado
                    {{ formatCLP(cuota?.pagado) }} de {{ formatCLP(cuota?.monto_cuota) }}
                </DialogDescription>
            </DialogHeader>

            <div class="flex flex-col gap-3">
                <div
                    v-for="pago in cuota?.pagos ?? []"
                    :key="pago.id"
                    class="flex flex-col gap-3 rounded-lg border p-4"
                    :class="{ 'opacity-60': pago.anulado_at }"
                >
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-medium" :class="{ 'line-through': pago.anulado_at }">
                                {{ formatCLP(pago.monto_pagado) }} ·
                                {{ formatDate(pago.fecha_pago) }}
                            </span>
                            <span class="text-xs text-muted-foreground">
                                {{ pago.medio_pago }}
                                <template v-if="pago.numero_comprobante">
                                    · Comprobante {{ pago.numero_comprobante }}
                                </template>
                                · Registrado por {{ pago.usuario?.name ?? '—' }}
                            </span>
                        </div>
                        <Badge :variant="pago.anulado_at ? 'destructive' : 'secondary'">
                            {{ pago.anulado_at ? 'Anulado' : 'Vigente' }}
                        </Badge>
                    </div>

                    <p v-if="pago.anulado_at" class="text-xs text-muted-foreground">
                        Anulado por {{ pago.anulador?.name ?? '—' }}:
                        {{ pago.motivo_anulacion }}
                    </p>

                    <template v-else>
                        <Button
                            v-if="anulando !== pago.id"
                            variant="outline"
                            size="sm"
                            class="self-start text-destructive hover:text-destructive"
                            @click="iniciarAnulacion(pago)"
                        >
                            Anular pago
                        </Button>

                        <div v-else class="flex flex-col gap-2">
                            <Input
                                v-model="form.motivo"
                                type="text"
                                placeholder="Motivo de la anulación"
                                :aria-invalid="!!form.errors.motivo"
                            />
                            <InputError :message="form.errors.motivo" />
                            <div class="flex gap-2">
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    :disabled="form.processing"
                                    @click="confirmarAnulacion(pago)"
                                >
                                    <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                                    Confirmar anulación
                                </Button>
                                <Button variant="outline" size="sm" @click="anulando = null">
                                    Cancelar
                                </Button>
                            </div>
                        </div>
                    </template>
                </div>

                <p
                    v-if="!(cuota?.pagos ?? []).length"
                    class="py-6 text-center text-sm text-muted-foreground"
                >
                    Esta cuota aún no tiene pagos.
                </p>
            </div>
        </DialogContent>
    </Dialog>
</template>
