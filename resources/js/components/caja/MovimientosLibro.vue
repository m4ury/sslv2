<script setup>
import { useForm } from '@inertiajs/vue3';
import { Loader2, Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { fechaLocal, formatCLP, formatDate } from '@/config/ficha';

const props = defineProps({
    // titulo, descripcion, singular, columns[], fields[]
    config: { type: Object, required: true },
    // paginador de Laravel
    items: { type: Object, required: true },
    // URL de alta (POST). La anulación es POST {url}/{id}/anular
    url: { type: String, required: true },
    puedeRegistrar: { type: Boolean, default: false },
});

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive';

/* ---------- Alta ---------- */

const abierto = ref(false);
const form = useForm({});

function valorPorDefecto(field) {
    if (field.default === 'hoy') return fechaLocal();
    return field.default ?? '';
}

function esRequerido(field) {
    return typeof field.required === 'function' ? field.required(form) : !!field.required;
}

function abrir() {
    form.defaults(
        Object.fromEntries(props.config.fields.map((f) => [f.name, valorPorDefecto(f)])),
    );
    form.reset();
    form.clearErrors();
    abierto.value = true;
}

function guardar() {
    form.post(props.url, {
        preserveScroll: true,
        onSuccess: () => {
            abierto.value = false;
        },
    });
}

/* ---------- Anulación ---------- */

const anulandoId = ref(null);
const itemAnulando = computed(
    () => props.items.data.find((i) => i.id === anulandoId.value) ?? null,
);
const anularForm = useForm({ motivo: '' });

function abrirAnulacion(item) {
    anularForm.reset();
    anularForm.clearErrors();
    anulandoId.value = item.id;
}

function confirmarAnulacion() {
    anularForm.post(`${props.url}/${anulandoId.value}/anular`, {
        preserveScroll: true,
        onSuccess: () => {
            anulandoId.value = null;
        },
    });
}

/* ---------- Celdas ---------- */

function celda(col, item) {
    if (col.format) return col.format(item);
    const valor = item[col.key];
    if (valor == null || valor === '') return '—';
    if (col.type === 'date') return formatDate(valor);
    if (col.type === 'money') return formatCLP(valor);
    return valor;
}
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-start justify-between gap-4">
            <div class="flex flex-col gap-1.5">
                <CardTitle>{{ config.titulo }}</CardTitle>
                <CardDescription>{{ config.descripcion }}</CardDescription>
            </div>
            <Button v-if="puedeRegistrar" size="sm" @click="abrir">
                <Plus class="size-4" />
                Registrar
            </Button>
        </CardHeader>

        <CardContent class="flex flex-col gap-4">
            <slot name="filtros" />

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead
                                v-for="col in config.columns"
                                :key="col.key"
                                :class="{ 'text-right': col.type === 'money' }"
                            >
                                {{ col.label }}
                            </TableHead>
                            <TableHead>Registrado por</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in items.data"
                            :key="item.id"
                            :class="{ 'opacity-60': item.anulado_at }"
                        >
                            <TableCell
                                v-for="col in config.columns"
                                :key="col.key"
                                :class="{
                                    'text-right font-medium': col.type === 'money',
                                    'line-through': col.type === 'money' && item.anulado_at,
                                }"
                            >
                                {{ celda(col, item) }}
                            </TableCell>
                            <TableCell>{{ item.usuario?.name ?? '—' }}</TableCell>
                            <TableCell>
                                <div class="flex flex-col gap-1">
                                    <Badge
                                        :variant="item.anulado_at ? 'destructive' : 'secondary'"
                                        class="self-start"
                                    >
                                        {{ item.anulado_at ? 'Anulado' : 'Vigente' }}
                                    </Badge>
                                    <span
                                        v-if="item.anulado_at"
                                        class="max-w-xs text-xs whitespace-normal text-muted-foreground"
                                    >
                                        {{ item.anulador?.name ?? '—' }}: {{ item.motivo_anulacion }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end">
                                    <Button
                                        v-if="!item.anulado_at && puedeRegistrar"
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive hover:text-destructive"
                                        @click="abrirAnulacion(item)"
                                    >
                                        Anular
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="items.data.length === 0">
                            <TableCell
                                :colspan="config.columns.length + 3"
                                class="h-24 text-center text-muted-foreground"
                            >
                                No hay registros con esos filtros.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div
                v-if="items.total > 0"
                class="flex flex-col items-center justify-between gap-3 sm:flex-row"
            >
                <p class="text-sm text-muted-foreground">
                    Mostrando {{ items.from }} a {{ items.to }} de {{ items.total }}
                </p>
                <Pagination :links="items.links" />
            </div>
        </CardContent>
    </Card>

    <!-- Alta -->
    <Dialog v-model:open="abierto">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Registrar {{ config.singular }}</DialogTitle>
                <DialogDescription>
                    Los campos marcados con <span class="text-destructive">*</span> son obligatorios.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-6" @submit.prevent="guardar">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div
                        v-for="field in config.fields"
                        :key="field.name"
                        class="flex flex-col gap-2"
                        :class="{ 'sm:col-span-2': field.span === 2 }"
                    >
                        <Label :for="`mov-${field.name}`">
                            {{ field.label }}
                            <span v-if="esRequerido(field)" class="text-destructive">*</span>
                        </Label>

                        <select
                            v-if="field.type === 'select'"
                            :id="`mov-${field.name}`"
                            v-model="form[field.name]"
                            :class="selectClass"
                            :aria-invalid="!!form.errors[field.name]"
                            :required="esRequerido(field)"
                        >
                            <option value="">Seleccione...</option>
                            <option v-for="o in field.options" :key="o" :value="o">{{ o }}</option>
                        </select>

                        <Input
                            v-else
                            :id="`mov-${field.name}`"
                            v-model="form[field.name]"
                            :type="field.type ?? 'text'"
                            :min="field.min"
                            :step="field.step"
                            :max="field.maxHoy ? fechaLocal() : undefined"
                            :placeholder="field.placeholder"
                            :aria-invalid="!!form.errors[field.name]"
                            :required="esRequerido(field)"
                        />

                        <InputError :message="form.errors[field.name]" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="abierto = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        Registrar
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Anulación -->
    <Dialog :open="!!itemAnulando" @update:open="(a) => !a && (anulandoId = null)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Anular {{ config.singular }}</DialogTitle>
                <DialogDescription>
                    {{ formatCLP(itemAnulando?.monto) }} · {{ itemAnulando?.descripcion }}. Quedará en el
                    historial como anulado y dejará de sumar a los totales.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-4" @submit.prevent="confirmarAnulacion">
                <div class="flex flex-col gap-2">
                    <Label for="mov-motivo">Motivo <span class="text-destructive">*</span></Label>
                    <Input
                        id="mov-motivo"
                        v-model="anularForm.motivo"
                        type="text"
                        :aria-invalid="!!anularForm.errors.motivo"
                        required
                    />
                    <InputError :message="anularForm.errors.motivo" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="anulandoId = null">Cancelar</Button>
                    <Button type="submit" variant="destructive" :disabled="anularForm.processing">
                        <Loader2 v-if="anularForm.processing" class="size-4 animate-spin" />
                        Anular
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>