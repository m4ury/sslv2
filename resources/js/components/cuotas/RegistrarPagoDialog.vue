<script setup>
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
import { watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { fechaLocal, formatCLP, formatPeriodo } from '@/config/ficha';

const props = defineProps({
    cuota: { type: Object, default: null },
    medios: { type: Array, required: true },
});

const emit = defineEmits(['close']);

const form = useForm({
    fecha_pago: '',
    monto_pagado: '',
    medio_pago: 'Efectivo',
    numero_comprobante: '',
});

// Cada vez que se abre para una cuota, el formulario parte con el saldo completo
watch(
    () => props.cuota?.id,
    (id) => {
        if (!id) return;
        form.defaults({
            fecha_pago: fechaLocal(),
            monto_pagado: props.cuota.saldo,
            medio_pago: 'Efectivo',
            numero_comprobante: '',
        });
        form.reset();
        form.clearErrors();
    },
);

function guardar() {
    form.post(`/cuotas/${props.cuota.id}/pagos`, {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
}

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive';
</script>

<template>
    <Dialog :open="!!cuota" @update:open="(abierto) => !abierto && emit('close')">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Registrar pago</DialogTitle>
                <DialogDescription>
                    {{ cuota?.socio?.persona?.full_name }} ·
                    {{ formatPeriodo(cuota?.periodo) }} · Saldo
                    {{ formatCLP(cuota?.saldo) }}
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-6" @submit.prevent="guardar">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <Label for="fecha_pago">
                            Fecha de pago <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="fecha_pago"
                            v-model="form.fecha_pago"
                            type="date"
                            :max="fechaLocal()"
                            :aria-invalid="!!form.errors.fecha_pago"
                            required
                        />
                        <InputError :message="form.errors.fecha_pago" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="monto_pagado">
                            Monto pagado <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="monto_pagado"
                            v-model="form.monto_pagado"
                            type="number"
                            step="1"
                            min="1"
                            :max="cuota?.saldo"
                            :aria-invalid="!!form.errors.monto_pagado"
                            required
                        />
                        <InputError :message="form.errors.monto_pagado" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="medio_pago">
                            Medio de pago <span class="text-destructive">*</span>
                        </Label>
                        <select
                            id="medio_pago"
                            v-model="form.medio_pago"
                            :class="selectClass"
                            :aria-invalid="!!form.errors.medio_pago"
                            required
                        >
                            <option v-for="medio in medios" :key="medio" :value="medio">
                                {{ medio }}
                            </option>
                        </select>
                        <InputError :message="form.errors.medio_pago" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="numero_comprobante">
                            N° de comprobante
                            <span
                                v-if="form.medio_pago !== 'Efectivo'"
                                class="text-destructive"
                                >*</span
                            >
                        </Label>
                        <Input
                            id="numero_comprobante"
                            v-model="form.numero_comprobante"
                            type="text"
                            :aria-invalid="!!form.errors.numero_comprobante"
                            :required="form.medio_pago !== 'Efectivo'"
                        />
                        <InputError :message="form.errors.numero_comprobante" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('close')">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        Registrar pago
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
