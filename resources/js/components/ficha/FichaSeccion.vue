<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { Loader2, Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { Textarea } from '@/components/ui/textarea';
import { formatDate } from '@/config/ficha';
import { useConfirm } from '@/composables/useConfirm';

const { confirmar } = useConfirm();

const props = defineProps({
    seccion: { type: Object, required: true },
    items: { type: Array, required: true },
    url: { type: String, required: true },
    soloLectura: { type: Boolean, default: false },
    sinEliminar: { type: Boolean, default: false },
});

const open = ref(false);
const editing = ref(null);
const isEdit = computed(() => !!editing.value);

// Al crear se ocultan los campos editOnly; al editar, los createOnly
const fields = computed(() =>
    props.seccion.fields.filter((f) =>
        isEdit.value ? !f.createOnly : !f.editOnly,
    ),
);

const form = useForm({});

// Solo se envían los campos visibles
form.transform((data) =>
    Object.fromEntries(fields.value.map((f) => [f.name, data[f.name]])),
);

async function eliminar(item) {
    const ok = await confirmar({
        titulo: `¿Eliminar este registro de ${props.seccion.singular}?`,
        descripcion: 'El registro dejará de mostrarse en la ficha.',
        confirmarTexto: 'Eliminar',
        destructivo: true,
    });

    if (ok) {
        router.delete(`${props.url}/${item.id}`, { preserveScroll: true });
    }
}

function valorInicial(field, item) {
    if (field.type === 'file') return null;
    const valor = item?.[field.name];
    if (valor == null) return '';
    return field.type === 'date' ? String(valor).substring(0, 10) : valor;
}

function abrir(item = null) {
    editing.value = item;
    form.defaults(
        Object.fromEntries(
            props.seccion.fields.map((f) => [f.name, valorInicial(f, item)]),
        ),
    );
    form.reset();
    form.clearErrors();
    open.value = true;
}

function guardar() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    };

    if (isEdit.value) {
        form.put(`${props.url}/${editing.value.id}`, options);
    } else {
        form.post(props.url, options);
    }
}

function opciones(field) {
    return (field.options ?? []).map((o) =>
        typeof o === 'string' ? { value: o, label: o } : o,
    );
}

function celda(col, item) {
    if (col.format) return col.format(item);
    const valor = item[col.key];
    if (valor == null || valor === '') return '—';
    return col.type === 'date' ? formatDate(valor) : valor;
}
</script>

<template>
    <Card class="w-full min-w-0">
        <CardHeader class="flex flex-col items-start justify-between gap-4 sm:flex-row">
            <div class="flex flex-col gap-1.5">
                <CardTitle>{{ seccion.titulo }}</CardTitle>
                <CardDescription>{{ seccion.descripcion }}</CardDescription>
            </div>
            <Button size="sm" @click="abrir()">
                <Plus class="size-4" />
                Agregar
            </Button>
        </CardHeader>

        <CardContent class="min-w-0">
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-for="col in seccion.columns" :key="col.key">
                                {{ col.label }}
                            </TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in items" :key="item.id">
                            <TableCell
                                v-for="col in seccion.columns"
                                :key="col.key"
                                :class="col.class"
                            >
                                <Badge
                                    v-if="col.badge"
                                    :variant="col.variant ? col.variant(item) : 'secondary'"
                                >
                                    {{ celda(col, item) }}
                                </Badge>
                                <template v-else>{{ celda(col, item) }}</template>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <slot name="acciones" :item="item" />
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title="Editar"
                                        @click="abrir(item)"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title="Eliminar"
                                        class="text-destructive hover:text-destructive"
                                        @click="eliminar(item)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="items.length === 0">
                            <TableCell
                                :colspan="seccion.columns.length + 1"
                                class="h-24 text-center text-muted-foreground"
                            >
                                {{ seccion.vacio }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </CardContent>
    </Card>

    <Dialog v-model:open="open">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>
                    {{ isEdit ? 'Editar' : 'Agregar' }} {{ seccion.singular }}
                </DialogTitle>
                <DialogDescription>
                    Los campos marcados con
                    <span class="text-destructive">*</span> son obligatorios.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-6" @submit.prevent="guardar">
                <div class="grid gap-4 md:grid-cols-2">
                    <template v-for="(field, i) in fields" :key="field.name">
                        <h3
                            v-if="field.group && field.group !== fields[i - 1]?.group"
                            class="border-b pt-2 pb-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase md:col-span-2"
                        >
                            {{ field.group }}
                        </h3>

                        <div
                            class="flex flex-col gap-2"
                            :class="{ 'md:col-span-2': field.span === 2 }"
                        >
                            <Label :for="`${seccion.singular}-${field.name}`">
                                {{ field.label }}
                                <span v-if="field.required" class="text-destructive">*</span>
                            </Label>

                            <Textarea
                                v-if="field.type === 'textarea'"
                                :id="`${seccion.singular}-${field.name}`"
                                v-model="form[field.name]"
                                rows="3"
                                :aria-invalid="!!form.errors[field.name]"
                            />

                            <select
                                v-else-if="field.type === 'select'"
                                :id="`${seccion.singular}-${field.name}`"
                                v-model="form[field.name]"
                                class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive"
                                :aria-invalid="!!form.errors[field.name]"
                                :required="field.required"
                            >
                                <option value="">
                                    {{ field.placeholder ?? 'Seleccione...' }}
                                </option>
                                <option
                                    v-for="o in opciones(field)"
                                    :key="o.value"
                                    :value="o.value"
                                >
                                    {{ o.label }}
                                </option>
                            </select>

                            <Input
                                v-else-if="field.type === 'file'"
                                :id="`${seccion.singular}-${field.name}`"
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                :aria-invalid="!!form.errors[field.name]"
                                :required="field.required"
                                @change="form[field.name] = $event.target.files[0] ?? null"
                            />

                            <Input
                                v-else
                                :id="`${seccion.singular}-${field.name}`"
                                v-model="form[field.name]"
                                :type="field.type ?? 'text'"
                                :step="field.step"
                                :min="field.min"
                                :max="field.max"
                                :aria-invalid="!!form.errors[field.name]"
                                :required="field.required"
                            />

                            <InputError :message="form.errors[field.name]" />
                        </div>
                    </template>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="open = false">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        {{ isEdit ? 'Guardar cambios' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
