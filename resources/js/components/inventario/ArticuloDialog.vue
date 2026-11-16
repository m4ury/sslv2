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

const props = defineProps({
    // Si viene, es edición; si no, creación
    articulo: { type: Object, default: null },
    categorias: { type: Array, required: true },
});

const open = defineModel('open', { type: Boolean, default: false });

const esEdicion = computed(() => !!props.articulo);

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive';

const form = useForm({
    codigo: '',
    nombre: '',
    categoria: '',
    unidad: 'unidad',
    ubicacion: '',
    stock_minimo: '',
    descripcion: '',
    cantidad_inicial: '',
    activo: 1,
});

// "activo" solo existe al editar y "cantidad inicial" solo al crear
form.transform((data) => {
    const { activo, cantidad_inicial: inicial, ...resto } = data;
    return esEdicion.value ? { ...resto, activo } : { ...resto, cantidad_inicial: inicial };
});

watch(open, (abierto) => {
    if (!abierto) return;

    const a = props.articulo;

    form.defaults({
        codigo: a?.codigo ?? '',
        nombre: a?.nombre ?? '',
        categoria: a?.categoria ?? '',
        unidad: a?.unidad ?? 'unidad',
        ubicacion: a?.ubicacion ?? '',
        stock_minimo: a?.stock_minimo ?? '',
        descripcion: a?.descripcion ?? '',
        cantidad_inicial: '',
        activo: a ? (a.activo ? 1 : 0) : 1,
    });
    form.reset();
    form.clearErrors();
});

function guardar() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    };

    if (esEdicion.value) {
        form.put(`/inventario/articulos/${props.articulo.id}`, options);
    } else {
        form.post('/inventario/articulos', options);
    }
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ esEdicion ? 'Editar artículo' : 'Nuevo artículo' }}</DialogTitle>
                <DialogDescription>
                    Los campos marcados con <span class="text-destructive">*</span> son obligatorios.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-6" @submit.prevent="guardar">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <Label for="art-codigo">Código</Label>
                        <Input
                            id="art-codigo"
                            v-model="form.codigo"
                            type="text"
                            placeholder="Automático si lo dejas vacío"
                            :aria-invalid="!!form.errors.codigo"
                        />
                        <InputError :message="form.errors.codigo" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="art-categoria">Categoría <span class="text-destructive">*</span></Label>
                        <select
                            id="art-categoria"
                            v-model="form.categoria"
                            :class="selectClass"
                            :aria-invalid="!!form.errors.categoria"
                            required
                        >
                            <option value="">Seleccione...</option>
                            <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <InputError :message="form.errors.categoria" />
                    </div>

                    <div class="flex flex-col gap-2 sm:col-span-2">
                        <Label for="art-nombre">Nombre <span class="text-destructive">*</span></Label>
                        <Input
                            id="art-nombre"
                            v-model="form.nombre"
                            type="text"
                            :aria-invalid="!!form.errors.nombre"
                            required
                        />
                        <InputError :message="form.errors.nombre" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="art-unidad">Unidad de medida <span class="text-destructive">*</span></Label>
                        <Input
                            id="art-unidad"
                            v-model="form.unidad"
                            type="text"
                            placeholder="unidad, caja, set..."
                            :aria-invalid="!!form.errors.unidad"
                            required
                        />
                        <InputError :message="form.errors.unidad" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="art-ubicacion">Ubicación</Label>
                        <Input
                            id="art-ubicacion"
                            v-model="form.ubicacion"
                            type="text"
                            placeholder="Sala de reuniones"
                        />
                        <InputError :message="form.errors.ubicacion" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="art-minimo">Stock mínimo (aviso)</Label>
                        <Input id="art-minimo" v-model="form.stock_minimo" type="number" step="1" min="0" />
                        <InputError :message="form.errors.stock_minimo" />
                    </div>

                    <div v-if="esEdicion" class="flex flex-col gap-2">
                        <Label for="art-activo">Estado <span class="text-destructive">*</span></Label>
                        <select id="art-activo" v-model="form.activo" :class="selectClass" required>
                            <option :value="1">Activo</option>
                            <option :value="0">Inactivo</option>
                        </select>
                        <InputError :message="form.errors.activo" />
                    </div>

                    <div v-else class="flex flex-col gap-2">
                        <Label for="art-inicial">Cantidad inicial (opcional)</Label>
                        <Input
                            id="art-inicial"
                            v-model="form.cantidad_inicial"
                            type="number"
                            step="1"
                            min="1"
                            placeholder="Lo que ya existe hoy"
                            :aria-invalid="!!form.errors.cantidad_inicial"
                        />
                        <InputError :message="form.errors.cantidad_inicial" />
                    </div>

                    <div class="flex flex-col gap-2 sm:col-span-2">
                        <Label for="art-desc">Descripción</Label>
                        <Textarea id="art-desc" v-model="form.descripcion" rows="3" />
                        <InputError :message="form.errors.descripcion" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="open = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        {{ esEdicion ? 'Guardar cambios' : 'Crear artículo' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
