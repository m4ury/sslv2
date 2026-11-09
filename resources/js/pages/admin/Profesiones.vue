<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Loader2, Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
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
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    profesiones: { type: Array, required: true },
    grupos: { type: Object, required: true },
    plantillas: { type: Object, default: () => ({}) },
});

const { confirmar } = useConfirm();

const open = ref(false);
const editing = ref(null);

const form = useForm({ nombre: '', es_admin: false, permisos: [] });

function abrir(profesion = null) {
    editing.value = profesion;
    form.defaults({
        nombre: profesion?.nombre ?? '',
        es_admin: !!profesion?.es_admin,
        permisos: [...(profesion?.permisos ?? [])],
    });
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

    if (editing.value) {
        form.put(`/admin/profesiones/${editing.value.id}`, options);
    } else {
        form.post('/admin/profesiones', options);
    }
}

function aplicarPlantilla(nombre) {
    form.permisos = [...props.plantillas[nombre]];
}

async function eliminar(profesion) {
    const ok = await confirmar({
        titulo: `¿Eliminar la profesión "${profesion.nombre}"?`,
        descripcion: 'Solo se puede eliminar si no tiene usuarios asignados.',
        confirmarTexto: 'Eliminar',
        destructivo: true,
    });

    if (ok) {
        router.delete(`/admin/profesiones/${profesion.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Profesiones" />

    <div class="mx-auto flex max-w-5xl flex-col gap-6 p-4 md:p-6">
        <Card>
            <CardHeader class="flex flex-row items-start justify-between gap-4">
                <div class="flex flex-col gap-1.5">
                    <CardTitle>Profesiones</CardTitle>
                    <CardDescription>
                        Cada profesión define qué puede hacer quien la tiene. Los
                        administradores tienen acceso a todo.
                    </CardDescription>
                </div>
                <Button size="sm" @click="abrir()">
                    <Plus class="size-4" />
                    Agregar
                </Button>
            </CardHeader>

            <CardContent>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Profesión</TableHead>
                                <TableHead>Acceso</TableHead>
                                <TableHead>Usuarios</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="p in profesiones" :key="p.id">
                                <TableCell class="font-medium">{{ p.nombre }}</TableCell>
                                <TableCell>
                                    <Badge v-if="p.es_admin">Administrador</Badge>
                                    <span v-else class="text-sm text-muted-foreground">
                                        {{ (p.permisos ?? []).length }} permisos
                                    </span>
                                </TableCell>
                                <TableCell>{{ p.usuarios_count }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button variant="ghost" size="icon" title="Editar" @click="abrir(p)">
                                            <Pencil class="size-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            title="Eliminar"
                                            class="text-destructive hover:text-destructive"
                                            @click="eliminar(p)"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="profesiones.length === 0">
                                <TableCell colspan="4" class="h-24 text-center text-muted-foreground">
                                    No hay profesiones registradas.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>

    <Dialog v-model:open="open">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ editing ? 'Editar' : 'Agregar' }} profesión</DialogTitle>
                <DialogDescription>
                    Marca lo que puede hacer quien tenga esta profesión.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-6" @submit.prevent="guardar">
                <div class="flex flex-col gap-2">
                    <Label for="nombre">
                        Nombre <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="nombre"
                        v-model="form.nombre"
                        type="text"
                        :aria-invalid="!!form.errors.nombre"
                        required
                    />
                    <InputError :message="form.errors.nombre" />
                </div>

                <label class="flex items-start gap-3 rounded-lg border p-4">
                    <input
                        v-model="form.es_admin"
                        type="checkbox"
                        class="mt-0.5 size-4 accent-primary"
                    />
                    <span class="flex flex-col gap-1">
                        <span class="text-sm font-medium">Administrador</span>
                        <span class="text-xs text-muted-foreground">
                            Acceso total al sistema, sin importar los permisos marcados.
                        </span>
                    </span>
                </label>

                <div v-if="!form.es_admin" class="flex flex-col gap-4">
                    <div v-if="Object.keys(plantillas).length" class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-muted-foreground">Plantillas:</span>
                        <Button
                            v-for="(_, nombre) in plantillas"
                            :key="nombre"
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="aplicarPlantilla(nombre)"
                        >
                            {{ nombre }}
                        </Button>
                        <Button type="button" variant="ghost" size="sm" @click="form.permisos = []">
                            Limpiar
                        </Button>
                    </div>

                    <section v-for="(permisos, grupo) in grupos" :key="grupo" class="flex flex-col gap-2">
                        <h3
                            class="border-b pb-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            {{ grupo }}
                        </h3>
                        <label
                            v-for="(descripcion, clave) in permisos"
                            :key="clave"
                            class="flex items-start gap-3 py-1 text-sm"
                        >
                            <input
                                v-model="form.permisos"
                                type="checkbox"
                                :value="clave"
                                class="mt-0.5 size-4 accent-primary"
                            />
                            <span>{{ descripcion }}</span>
                        </label>
                    </section>

                    <InputError :message="form.errors.permisos" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="open = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        {{ editing ? 'Guardar cambios' : 'Crear profesión' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>