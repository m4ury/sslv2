<script setup>
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
import { computed, watch } from 'vue';
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
import { Textarea } from '@/components/ui/textarea';
import { fechaLocal } from '@/config/ficha';

const props = defineProps({
    articulo: { type: Object, default: null },
    // entrada | baja | prestamo | devolucion (null = cerrado)
    tipo: { type: String, default: null },
    // préstamo que se está devolviendo
    prestamo: { type: Object, default: null },
    opciones: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const TITULOS = {
    entrada: 'Registrar entrada',
    baja: 'Dar de baja',
    prestamo: 'Registrar préstamo',
    devolucion: 'Registrar devolución',
};

const CAMPOS = {
    entrada: ['motivo', 'origen', 'valor_unitario', 'tipo_documento', 'num_documento'],
    baja: ['motivo'],
    prestamo: ['prestatario', 'prestatario_telefono', 'fecha_compromiso'],
    devolucion: ['movimiento_origen_id'],
};

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive';

const maximo = computed(() => {
    if (props.tipo === 'devolucion') return props.prestamo?.pendiente;
    if (props.tipo === 'baja' || props.tipo === 'prestamo') return props.articulo?.disponible;
    return undefined;
});

const ayuda = computed(() => {
    if (props.tipo === 'devolucion') return `Pendiente de este préstamo: ${props.prestamo?.pendiente}.`;
    if (props.tipo === 'baja' || props.tipo === 'prestamo') return `Disponible: ${props.articulo?.disponible}.`;
    return null;
});

const form = useForm({
    tipo: '',
    fecha: '',
    cantidad: '',
    observaciones: '',
    motivo: '',
    origen: '',
    valor_unitario: '',
    tipo_documento: '',
    num_documento: '',
    prestatario: '',
    prestatario_telefono: '',
    fecha_compromiso: '',
    movimiento_origen_id: '',
});

// Solo se envían los campos del tipo de movimiento
form.transform((data) => {
    const permitidos = ['tipo', 'fecha', 'cantidad', 'observaciones', ...(CAMPOS[props.tipo] ?? [])];
    return Object.fromEntries(permitidos.map((k) => [k, data[k]]));
});

watch(
    () => [props.tipo, props.articulo?.id, props.prestamo?.id],
    () => {
        if (!props.tipo) return;

        form.defaults({
            tipo: props.tipo,
            fecha: fechaLocal(),
            cantidad: props.tipo === 'devolucion' ? (props.prestamo?.pendiente ?? '') : '',
            observaciones: '',
            motivo: props.tipo === 'entrada' ? 'Compra' : props.tipo === 'baja' ? 'Desgaste' : '',
            origen: '',
            valor_unitario: '',
            tipo_documento: '',
            num_documento: '',
            prestatario: '',
            prestatario_telefono: '',
            fecha_compromiso: '',
            movimiento_origen_id: props.prestamo?.id ?? '',
        });
        form.reset();
        form.clearErrors();
    },
    { immediate: true },
);

const documentoRequerido = computed(
    () => !!form.tipo_documento && form.tipo_documento !== 'Sin documento',
);

function guardar() {
    form.post(`/inventario/${props.articulo.id}/movimientos`, {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
}
</script>

<template>
    <Dialog :open="!!tipo" @update:open="(abierto) => !abierto && emit('close')">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>{{ TITULOS[tipo] }}</DialogTitle>
                <DialogDescription>
                    {{ articulo?.nombre }}
                    <span v-if="prestamo"> · prestado a {{ prestamo.prestatario }}</span>
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-6" @submit.prevent="guardar">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <Label for="mov-fecha">Fecha <span class="text-destructive">*</span></Label>
                        <Input
                            id="mov-fecha"
                            v-model="form.fecha"
                            type="date"
                            :max="fechaLocal()"
                            :aria-invalid="!!form.errors.fecha"
                            required
                        />
                        <InputError :message="form.errors.fecha" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="mov-cantidad">
                            Cantidad ({{ articulo?.unidad }}) <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="mov-cantidad"
                            v-model="form.cantidad"
                            type="number"
                            step="1"
                            min="1"
                            :max="maximo"
                            :aria-invalid="!!form.errors.cantidad"
                            required
                        />
                        <p v-if="ayuda" class="text-xs text-muted-foreground">{{ ayuda }}</p>
                        <InputError :message="form.errors.cantidad" />
                    </div>

                    <!-- Entrada y baja -->
                    <div v-if="tipo === 'entrada' || tipo === 'baja'" class="flex flex-col gap-2 sm:col-span-2">
                        <Label for="mov-motivo">Motivo <span class="text-destructive">*</span></Label>
                        <select
                            id="mov-motivo"
                            v-model="form.motivo"
                            :class="selectClass"
                            :aria-invalid="!!form.errors.motivo"
                            required
                        >
                            <option
                                v-for="m in tipo === 'entrada' ? opciones.motivosEntrada : opciones.motivosBaja"
                                :key="m"
                                :value="m"
                            >
                                {{ m }}
                            </option>
                        </select>
                        <InputError :message="form.errors.motivo" />
                    </div>

                    <!-- Entrada -->
                    <template v-if="tipo === 'entrada'">
                        <div class="flex flex-col gap-2">
                            <Label for="mov-origen">Proveedor o donante</Label>
                            <Input id="mov-origen" v-model="form.origen" type="text" />
                            <InputError :message="form.errors.origen" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="mov-valor">Valor unitario (opcional)</Label>
                            <Input id="mov-valor" v-model="form.valor_unitario" type="number" step="1" min="0" />
                            <InputError :message="form.errors.valor_unitario" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="mov-tipo-doc">Documento</Label>
                            <select id="mov-tipo-doc" v-model="form.tipo_documento" :class="selectClass">
                                <option value="">Sin indicar</option>
                                <option v-for="t in opciones.tiposDocumento" :key="t" :value="t">{{ t }}</option>
                            </select>
                            <InputError :message="form.errors.tipo_documento" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="mov-num-doc">
                                N° de documento
                                <span v-if="documentoRequerido" class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="mov-num-doc"
                                v-model="form.num_documento"
                                type="text"
                                :aria-invalid="!!form.errors.num_documento"
                                :required="documentoRequerido"
                            />
                            <InputError :message="form.errors.num_documento" />
                        </div>
                    </template>

                    <!-- Préstamo -->
                    <template v-if="tipo === 'prestamo'">
                        <div class="flex flex-col gap-2 sm:col-span-2">
                            <Label for="mov-prestatario">
                                Prestado a <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="mov-prestatario"
                                v-model="form.prestatario"
                                type="text"
                                placeholder="Nombre de la persona u organización"
                                :aria-invalid="!!form.errors.prestatario"
                                required
                            />
                            <InputError :message="form.errors.prestatario" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="mov-telefono">Teléfono</Label>
                            <Input id="mov-telefono" v-model="form.prestatario_telefono" type="tel" />
                            <InputError :message="form.errors.prestatario_telefono" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="mov-compromiso">Devolver el</Label>
                            <Input
                                id="mov-compromiso"
                                v-model="form.fecha_compromiso"
                                type="date"
                                :min="form.fecha || undefined"
                                :aria-invalid="!!form.errors.fecha_compromiso"
                            />
                            <InputError :message="form.errors.fecha_compromiso" />
                        </div>
                    </template>

                    <div class="flex flex-col gap-2 sm:col-span-2">
                        <Label for="mov-obs">
                            {{ tipo === 'baja' ? 'Justificación' : 'Observaciones' }}
                            <span v-if="tipo === 'baja'" class="text-destructive">*</span>
                        </Label>
                        <Textarea
                            id="mov-obs"
                            v-model="form.observaciones"
                            rows="3"
                            :placeholder="tipo === 'devolucion' ? 'Estado en que vuelve el artículo' : ''"
                            :aria-invalid="!!form.errors.observaciones"
                            :required="tipo === 'baja'"
                        />
                        <InputError :message="form.errors.observaciones" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('close')">Cancelar</Button>
                    <Button
                        type="submit"
                        :variant="tipo === 'baja' ? 'destructive' : 'default'"
                        :disabled="form.processing"
                    >
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        Registrar
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
