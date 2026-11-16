<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Pencil, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import ArticuloDialog from '@/components/inventario/ArticuloDialog.vue';
import MovimientoDialog from '@/components/inventario/MovimientoDialog.vue';
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
import { useConfirm } from '@/composables/useConfirm';
import { usePermisos } from '@/composables/usePermisos';
import { formatCLP, formatDate } from '@/config/ficha';

const props = defineProps({
    articulo: { type: Object, required: true },
    kardex: { type: Array, default: () => [] },
    prestamos: { type: Array, default: () => [] },
    opciones: { type: Object, required: true },
    categorias: { type: Array, default: () => [] },
});

const { puede } = usePermisos();
const { confirmar } = useConfirm();

const ETIQUETAS = { entrada: 'Entrada', baja: 'Baja', prestamo: 'Préstamo', devolucion: 'Devolución' };
const VARIANTES = { entrada: 'secondary', baja: 'destructive', prestamo: 'default', devolucion: 'outline' };
const ESTADOS_PRESTAMO = { Vigente: 'default', Vencido: 'destructive', Devuelto: 'secondary' };

const bajoStock = computed(
    () =>
        props.articulo.activo &&
        props.articulo.stock_minimo !== null &&
        props.articulo.disponible < props.articulo.stock_minimo,
);

/* ---------- Movimientos ---------- */

const tipoDialogo = ref(null);
const prestamoSel = ref(null);

function abrirMovimiento(tipo, prestamo = null) {
    prestamoSel.value = prestamo;
    tipoDialogo.value = tipo;
}

function cerrarMovimiento() {
    tipoDialogo.value = null;
    prestamoSel.value = null;
}

function detalle(m) {
    const partes = [];

    if (m.tipo === 'entrada') {
        if (m.origen) partes.push(m.origen);
        if (m.valor_unitario) partes.push(`${formatCLP(m.valor_unitario)} c/u`);
        if (m.tipo_documento) {
            partes.push(`${m.tipo_documento}${m.num_documento ? ` N° ${m.num_documento}` : ''}`);
        }
    }

    if (m.tipo === 'prestamo') {
        partes.push(`A ${m.prestatario}`);
        if (m.fecha_compromiso) partes.push(`devolver el ${formatDate(m.fecha_compromiso)}`);
    }

    if (m.observaciones) partes.push(m.observaciones);

    return partes.join(' · ') || '—';
}

/* ---------- Anulación ---------- */

const anulandoId = ref(null);
const movAnulando = computed(() => props.kardex.find((m) => m.id === anulandoId.value) ?? null);
const anularForm = useForm({ motivo: '' });

function abrirAnulacion(m) {
    anularForm.reset();
    anularForm.clearErrors();
    anulandoId.value = m.id;
}

function confirmarAnulacion() {
    anularForm.post(`/inventario/movimientos/${anulandoId.value}/anular`, {
        preserveScroll: true,
        onSuccess: () => {
            anulandoId.value = null;
        },
    });
}

/* ---------- Artículo ---------- */

const editando = ref(false);

async function eliminar() {
    const ok = await confirmar({
        titulo: `¿Eliminar "${props.articulo.nombre}"?`,
        descripcion: 'Solo se puede eliminar si no tiene movimientos. Si ya los tiene, márcalo como inactivo.',
        confirmarTexto: 'Eliminar',
        destructivo: true,
    });

    if (ok) router.delete(`/inventario/articulos/${props.articulo.id}`);
}
</script>

<template>
    <Head :title="articulo.nombre" />

    <div class="mx-auto flex max-w-6xl flex-col gap-6 p-4 md:p-6">
        <!-- Encabezado -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold">{{ articulo.nombre }}</h1>
                <div class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                    <span class="font-mono">{{ articulo.codigo }}</span>
                    <span>· {{ articulo.categoria }}</span>
                    <span v-if="articulo.ubicacion">· {{ articulo.ubicacion }}</span>
                    <Badge v-if="!articulo.activo" variant="outline">Inactivo</Badge>
                    <Badge v-if="bajoStock" variant="destructive">Bajo stock mínimo</Badge>
                </div>
                <p v-if="articulo.descripcion" class="max-w-2xl text-sm">{{ articulo.descripcion }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button as-child variant="outline">
                    <Link href="/inventario">
                        <ArrowLeft class="size-4" />
                        Volver
                    </Link>
                </Button>
                <template v-if="puede('inventario.articulos')">
                    <Button variant="outline" @click="editando = true">
                        <Pencil class="size-4" />
                        Editar
                    </Button>
                    <Button variant="outline" class="text-destructive hover:text-destructive" @click="eliminar">
                        <Trash2 class="size-4" />
                        Eliminar
                    </Button>
                </template>
            </div>
        </div>

        <!-- Stock -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader>
                    <CardDescription>Existencia</CardDescription>
                    <CardTitle class="text-2xl">
                        {{ articulo.existencia }}
                        <span class="text-sm font-normal text-muted-foreground">{{ articulo.unidad }}</span>
                    </CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>Disponible</CardDescription>
                    <CardTitle class="text-2xl">{{ articulo.disponible }}</CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>En préstamo</CardDescription>
                    <CardTitle class="text-2xl">{{ articulo.en_prestamo }}</CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>Stock mínimo</CardDescription>
                    <CardTitle class="text-2xl">{{ articulo.stock_minimo ?? '—' }}</CardTitle>
                </CardHeader>
            </Card>
        </div>

        <!-- Acciones -->
        <div v-if="puede('inventario.registrar')" class="flex flex-wrap gap-2">
            <Button @click="abrirMovimiento('entrada')">Registrar entrada</Button>
            <Button variant="outline" :disabled="articulo.disponible < 1" @click="abrirMovimiento('prestamo')">
                Registrar préstamo
            </Button>
            <Button
                variant="outline"
                class="text-destructive hover:text-destructive"
                :disabled="articulo.disponible < 1"
                @click="abrirMovimiento('baja')"
            >
                Dar de baja
            </Button>
        </div>

        <!-- Préstamos pendientes -->
        <Card v-if="prestamos.length">
            <CardHeader>
                <CardTitle>Préstamos pendientes</CardTitle>
                <CardDescription>Unidades que están fuera del centro.</CardDescription>
            </CardHeader>
            <CardContent class="flex flex-col gap-3">
                <div
                    v-for="p in prestamos"
                    :key="p.id"
                    class="flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium">{{ p.prestatario }}</span>
                        <span class="text-xs text-muted-foreground">
                            {{ p.pendiente }} de {{ p.cantidad }} por devolver · desde
                            {{ formatDate(p.fecha) }}
                            <template v-if="p.fecha_compromiso">
                                · devolver el {{ formatDate(p.fecha_compromiso) }}
                            </template>
                            <template v-if="p.telefono"> · {{ p.telefono }}</template>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge :variant="ESTADOS_PRESTAMO[p.estado]">{{ p.estado }}</Badge>
                        <Button
                            v-if="puede('inventario.registrar')"
                            size="sm"
                            @click="abrirMovimiento('devolucion', p)"
                        >
                            Registrar devolución
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Kardex -->
        <Card>
            <CardHeader>
                <CardTitle>Historial de movimientos</CardTitle>
                <CardDescription>
                    Del más reciente al más antiguo. El saldo es el disponible después de cada movimiento.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Fecha</TableHead>
                                <TableHead>Movimiento</TableHead>
                                <TableHead>Detalle</TableHead>
                                <TableHead class="text-right">Cantidad</TableHead>
                                <TableHead class="text-right">Saldo</TableHead>
                                <TableHead>Registrado por</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="m in kardex" :key="m.id" :class="{ 'opacity-60': m.anulado_at }">
                                <TableCell>{{ formatDate(m.fecha) }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-col gap-1">
                                        <Badge :variant="VARIANTES[m.tipo]" class="self-start">
                                            {{ ETIQUETAS[m.tipo] }}
                                        </Badge>
                                        <span v-if="m.motivo" class="text-xs text-muted-foreground">
                                            {{ m.motivo }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell class="max-w-sm whitespace-normal">
                                    {{ detalle(m) }}
                                    <span
                                        v-if="m.anulado_at"
                                        class="mt-1 block text-xs text-destructive"
                                    >
                                        Anulado por {{ m.anulador?.name ?? '—' }}: {{ m.motivo_anulacion }}
                                    </span>
                                </TableCell>
                                <TableCell
                                    class="text-right font-medium"
                                    :class="{ 'line-through': m.anulado_at }"
                                >
                                    {{ m.tipo === 'entrada' || m.tipo === 'devolucion' ? '+' : '−' }}{{ m.cantidad }}
                                </TableCell>
                                <TableCell class="text-right">{{ m.saldo ?? '—' }}</TableCell>
                                <TableCell>{{ m.usuario?.name ?? '—' }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end">
                                        <Button
                                            v-if="!m.anulado_at && puede('inventario.registrar')"
                                            variant="ghost"
                                            size="sm"
                                            class="text-destructive hover:text-destructive"
                                            @click="abrirAnulacion(m)"
                                        >
                                            Anular
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="kardex.length === 0">
                                <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
                                    Este artículo aún no tiene movimientos. Registra una entrada para
                                    empezar.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>

    <MovimientoDialog
        :articulo="articulo"
        :tipo="tipoDialogo"
        :prestamo="prestamoSel"
        :opciones="opciones"
        @close="cerrarMovimiento"
    />

    <ArticuloDialog v-model:open="editando" :articulo="articulo" :categorias="categorias" />

    <!-- Anular movimiento -->
    <Dialog :open="!!movAnulando" @update:open="(a) => !a && (anulandoId = null)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Anular movimiento</DialogTitle>
                <DialogDescription>
                    {{ ETIQUETAS[movAnulando?.tipo] }} de {{ movAnulando?.cantidad }}. Quedará en el
                    historial como anulado y el stock se recalculará.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-4" @submit.prevent="confirmarAnulacion">
                <div class="flex flex-col gap-2">
                    <Label for="inv-motivo">Motivo <span class="text-destructive">*</span></Label>
                    <Input
                        id="inv-motivo"
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
