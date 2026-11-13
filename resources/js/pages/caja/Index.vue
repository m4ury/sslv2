<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { FileText, Loader2, Plus, Settings } from '@lucide/vue';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
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
import { usePermisos } from '@/composables/usePermisos';

const props = defineProps({
    configuracion: { type: Object, default: null },
    configEditable: { type: Boolean, default: true },
    saldo: { type: Number, default: 0 },
    mes: { type: Object, required: true },
    egresos: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    categorias: { type: Array, default: () => [] },
    tiposDocumento: { type: Array, default: () => [] },
    medios: { type: Array, default: () => [] },
});

const { puede } = usePermisos();

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive';

/* ---------- Filtros ---------- */

const filtros = reactive({
    search: props.filters.search ?? '',
    categoria: props.filters.categoria ?? '',
    estado: props.filters.estado ?? '',
    desde: props.filters.desde ?? '',
    hasta: props.filters.hasta ?? '',
});

let timeout;

watch(filtros, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const params = Object.fromEntries(
            Object.entries(filtros).filter(([, v]) => v !== '' && v != null),
        );
        router.get('/caja', params, { preserveState: true, preserveScroll: true, replace: true });
    }, 300);
});

onBeforeUnmount(() => clearTimeout(timeout));

/* ---------- Registrar egreso ---------- */

const registrando = ref(false);

const form = useForm({
    fecha_egreso: '',
    monto: '',
    categoria: '',
    descripcion: '',
    proveedor: '',
    tipo_documento: 'Boleta',
    num_documento: '',
    medio_pago: 'Efectivo',
});

function abrirRegistro() {
    form.defaults({
        fecha_egreso: fechaLocal(),
        monto_egreso: '',
        categoria: '',
        descripcion_egreso: '',
        proveedor: '',
        tipo_documento: 'Boleta',
        num_documento: '',
        medio_pago: 'Efectivo',
    });
    form.reset();
    form.clearErrors();
    registrando.value = true;
}

function guardar() {
    form.post('/caja/egresos', {
        preserveScroll: true,
        onSuccess: () => {
            registrando.value = false;
        },
    });
}

/* ---------- Anular egreso ---------- */

const anulandoId = ref(null);
const egresoAnulando = computed(
    () => props.egresos.data.find((e) => e.id === anulandoId.value) ?? null,
);
const anularForm = useForm({ motivo: '' });

function abrirAnulacion(egreso) {
    anularForm.reset();
    anularForm.clearErrors();
    anulandoId.value = egreso.id;
}

function confirmarAnulacion() {
    anularForm.post(`/caja/egresos/${anulandoId.value}/anular`, {
        preserveScroll: true,
        onSuccess: () => {
            anulandoId.value = null;
        },
    });
}

/* ---------- Saldo inicial ---------- */

const configurando = ref(false);
const configForm = useForm({ fecha_inicio: '', saldo_inicial: 0 });

function abrirConfig() {
    configForm.defaults({
        fecha_inicio: props.configuracion?.fecha_inicio ?? fechaLocal(),
        saldo_inicial: props.configuracion?.saldo_inicial ?? 0,
    });
    configForm.reset();
    configForm.clearErrors();
    configurando.value = true;
}

function guardarConfig() {
    configForm.put('/caja/configuracion', {
        preserveScroll: true,
        onSuccess: () => {
            configurando.value = false;
        },
    });
}
</script>

<template>
    <Head title="Caja" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <!-- Encabezado -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Flujo de caja</h1>
                <p class="text-sm text-muted-foreground">
                    Saldo = saldo inicial + cuotas y otros ingresos − egresos.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button
                    v-if="puede('caja.cerrar') && configEditable"
                    variant="outline"
                    @click="abrirConfig"
                >
                    <Settings class="size-4" />
                    Saldo inicial
                </Button>
                <Button as-child variant="outline">
                    <Link href="/caja/estado">
                        <FileText class="size-4" />
                        Estado de cuentas
                    </Link>
                </Button>
                <Button as-child variant="outline">
                    <Link href="/caja/ingresos">Otros ingresos</Link>
                </Button>
                <Button v-if="puede('caja.registrar') && configuracion" @click="abrirRegistro">
                    <Plus class="size-4" />
                    Registrar egreso
                </Button>
            </div>
        </div>

        <!-- Caja sin configurar -->
        <Card v-if="!configuracion">
            <CardHeader>
                <CardTitle>Falta definir el saldo inicial</CardTitle>
                <CardDescription>
                    Indica con cuánto dinero y desde qué fecha empieza a llevarse la caja en el
                    sistema.
                    <span v-if="!puede('caja.cerrar')">Pídeselo a un administrador.</span>
                </CardDescription>
            </CardHeader>
            <CardContent v-if="puede('caja.cerrar')">
                <Button @click="abrirConfig">Definir saldo inicial</Button>
            </CardContent>
        </Card>

        <template v-else>
            <!-- Resumen -->
            <div class="grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardDescription>Saldo disponible hoy</CardDescription>
                        <CardTitle class="text-2xl" :class="{ 'text-destructive': saldo < 0 }">
                            {{ formatCLP(saldo) }}
                        </CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Ingresos del mes</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ formatCLP(mes.cuotas + mes.otros) }}
                        </CardTitle>
                        <p class="text-xs text-muted-foreground">
                            Cuotas {{ formatCLP(mes.cuotas) }} · Aportes {{ formatCLP(mes.otros) }}
                        </p>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Egresos del mes</CardDescription>
                        <CardTitle class="text-2xl">{{ formatCLP(mes.egresos) }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <!-- Egresos -->
            <Card>
                <CardHeader>
                    <CardTitle>Egresos</CardTitle>
                    <CardDescription>Gastos de funcionamiento de la agrupación.</CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                        <Input
                            v-model="filtros.search"
                            type="search"
                            placeholder="Buscar por descripción, proveedor o documento..."
                            class="lg:max-w-sm lg:flex-1"
                        />
                        <select v-model="filtros.categoria" :class="selectClass" class="lg:w-48">
                            <option value="">Todas las categorías</option>
                            <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <select v-model="filtros.estado" :class="selectClass" class="lg:w-44">
                            <option value="">Vigentes y anulados</option>
                            <option value="Vigente">Solo vigentes</option>
                            <option value="Anulado">Solo anulados</option>
                        </select>
                        <div class="flex items-center gap-2">
                            <Input v-model="filtros.desde" type="date" aria-label="Desde" />
                            <span class="text-sm text-muted-foreground">a</span>
                            <Input v-model="filtros.hasta" type="date" aria-label="Hasta" />
                        </div>
                    </div>

                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Categoría</TableHead>
                                    <TableHead>Descripción</TableHead>
                                    <TableHead>Documento</TableHead>
                                    <TableHead class="text-right">Monto</TableHead>
                                    <TableHead>Registrado por</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="e in egresos.data"
                                    :key="e.id"
                                    :class="{ 'opacity-60': e.anulado_at }"
                                >
                                    <TableCell>{{ formatDate(e.fecha_egreso) }}</TableCell>
                                    <TableCell>{{ e.categoria }}</TableCell>
                                    <TableCell>
                                        <div class="flex max-w-xs flex-col whitespace-normal">
                                            <span>{{ e.descripcion_egreso }}</span>
                                            <span v-if="e.proveedor" class="text-xs text-muted-foreground">
                                                {{ e.proveedor }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{ e.tipo_documento }}
                                        <span v-if="e.numero_documento">N° {{ e.numero_documento }}</span>
                                    </TableCell>
                                    <TableCell
                                        class="text-right font-medium"
                                        :class="{ 'line-through': e.anulado_at }"
                                    >
                                        {{ formatCLP(e.monto_egreso) }}
                                    </TableCell>
                                    <TableCell>{{ e.usuario?.name ?? '—' }}</TableCell>
                                    <TableCell>
                                        <div class="flex flex-col gap-1">
                                            <Badge
                                                :variant="e.anulado_at ? 'destructive' : 'secondary'"
                                                class="self-start"
                                            >
                                                {{ e.anulado_at ? 'Anulado' : 'Vigente' }}
                                            </Badge>
                                            <span
                                                v-if="e.anulado_at"
                                                class="max-w-xs text-xs whitespace-normal text-muted-foreground"
                                            >
                                                {{ e.anulador?.name ?? '—' }}: {{ e.motivo_anulacion }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex justify-end">
                                            <Button
                                                v-if="!e.anulado_at && puede('caja.registrar')"
                                                variant="ghost"
                                                size="sm"
                                                class="text-destructive hover:text-destructive"
                                                @click="abrirAnulacion(e)"
                                            >
                                                Anular
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="egresos.data.length === 0">
                                    <TableCell colspan="8" class="h-24 text-center text-muted-foreground">
                                        No hay egresos con esos filtros.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div
                        v-if="egresos.total > 0"
                        class="flex flex-col items-center justify-between gap-3 sm:flex-row"
                    >
                        <p class="text-sm text-muted-foreground">
                            Mostrando {{ egresos.from }} a {{ egresos.to }} de {{ egresos.total }}
                        </p>
                        <Pagination :links="egresos.links" />
                    </div>
                </CardContent>
            </Card>
        </template>
    </div>

    <!-- Registrar egreso -->
    <Dialog v-model:open="registrando">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Registrar egreso</DialogTitle>
                <DialogDescription>
                    Saldo disponible: {{ formatCLP(saldo) }}. Los campos con
                    <span class="text-destructive">*</span> son obligatorios.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-6" @submit.prevent="guardar">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <Label for="fecha_egreso">Fecha <span class="text-destructive">*</span></Label>
                        <Input
                            id="fecha_egreso"
                            v-model="form.fecha_egreso"
                            type="date"
                            :max="fechaLocal()"
                            :aria-invalid="!!form.errors.fecha_egreso"
                            required
                        />
                        <InputError :message="form.errors.fecha_egreso" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="monto_egreso">Monto <span class="text-destructive">*</span></Label>
                        <Input
                            id="monto_egreso"
                            v-model="form.monto_egreso"
                            type="number"
                            step="1"
                            min="1"
                            :aria-invalid="!!form.errors.monto_egreso"
                            required
                        />
                        <InputError :message="form.errors.monto_egreso" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="categoria">Categoría <span class="text-destructive">*</span></Label>
                        <select
                            id="categoria"
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

                    <div class="flex flex-col gap-2">
                        <Label for="proveedor">Proveedor</Label>
                        <Input id="proveedor" v-model="form.proveedor" type="text" />
                        <InputError :message="form.errors.proveedor" />
                    </div>

                    <div class="flex flex-col gap-2 sm:col-span-2">
                        <Label for="descripcion_egreso">Descripción <span class="text-destructive">*</span></Label>
                        <Input
                            id="descripcion_egreso"
                            v-model="form.descripcion_egreso"
                            type="text"
                            placeholder="Pago de luz de marzo"
                            :aria-invalid="!!form.errors.descripcion_egreso"
                            required
                        />
                        <InputError :message="form.errors.descripcion_egreso" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="tipo_documento">Documento <span class="text-destructive">*</span></Label>
                        <select
                            id="tipo_documento"
                            v-model="form.tipo_documento"
                            :class="selectClass"
                            required
                        >
                            <option v-for="t in tiposDocumento" :key="t" :value="t">{{ t }}</option>
                        </select>
                        <InputError :message="form.errors.tipo_documento" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="num_documento">
                            N° de documento
                            <span v-if="form.tipo_documento !== 'Sin documento'" class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="num_documento"
                            v-model="form.num_documento"
                            type="text"
                            :aria-invalid="!!form.errors.num_documento"
                            :required="form.tipo_documento !== 'Sin documento'"
                        />
                        <InputError :message="form.errors.num_documento" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="medio_pago">Medio de pago <span class="text-destructive">*</span></Label>
                        <select id="medio_pago" v-model="form.medio_pago" :class="selectClass" required>
                            <option v-for="m in medios" :key="m" :value="m">{{ m }}</option>
                        </select>
                        <InputError :message="form.errors.medio_pago" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="registrando = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        Registrar egreso
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Anular egreso -->
    <Dialog :open="!!egresoAnulando" @update:open="(abierto) => !abierto && (anulandoId = null)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Anular egreso</DialogTitle>
                <DialogDescription>
                    {{ formatCLP(egresoAnulando?.monto_egreso) }} · {{ egresoAnulando?.descripcion_egreso }}. Quedará en el
                    historial como anulado y el dinero volverá al saldo.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-4" @submit.prevent="confirmarAnulacion">
                <div class="flex flex-col gap-2">
                    <Label for="motivo">Motivo <span class="text-destructive">*</span></Label>
                    <Input
                        id="motivo"
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
                        Anular egreso
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Saldo inicial -->
    <Dialog v-model:open="configurando">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Saldo inicial de caja</DialogTitle>
                <DialogDescription>
                    Dinero con que parte el sistema. Solo se cuentan los movimientos desde esa fecha. No se
                    puede modificar una vez que hay meses cerrados.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-4" @submit.prevent="guardarConfig">
                <div class="flex flex-col gap-2">
                    <Label for="fecha_inicio">Fecha de inicio <span class="text-destructive">*</span></Label>
                    <Input
                        id="fecha_inicio"
                        v-model="configForm.fecha_inicio"
                        type="date"
                        :aria-invalid="!!configForm.errors.fecha_inicio"
                        required
                    />
                    <InputError :message="configForm.errors.fecha_inicio" />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="saldo_inicial">Saldo inicial <span class="text-destructive">*</span></Label>
                    <Input
                        id="saldo_inicial"
                        v-model="configForm.saldo_inicial"
                        type="number"
                        step="1"
                        min="0"
                        :aria-invalid="!!configForm.errors.saldo_inicial"
                        required
                    />
                    <InputError :message="configForm.errors.saldo_inicial" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="configurando = false">Cancelar</Button>
                    <Button type="submit" :disabled="configForm.processing">
                        <Loader2 v-if="configForm.processing" class="size-4 animate-spin" />
                        Guardar
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
