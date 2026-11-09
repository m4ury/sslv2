<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { History, Search, Settings, Wallet } from '@lucide/vue';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import Pagination from '@/components/Pagination.vue';
import PagosDialog from '@/components/cuotas/PagosDialog.vue';
import RegistrarPagoDialog from '@/components/cuotas/RegistrarPagoDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCLP, formatDate, formatPeriodo } from '@/config/ficha';

const props = defineProps({
    cuotas: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    periodos: { type: Array, default: () => [] },
    configuraciones: { type: Array, default: () => [] },
    medios: { type: Array, default: () => [] },
    resumen: { type: Object, required: true },
});

const ESTADOS = ['Pendiente', 'Parcial', 'Vencida', 'Pagada'];

const VARIANTES = {
    Pagada: 'secondary',
    Pendiente: 'outline',
    Parcial: 'default',
    Vencida: 'destructive',
};

/* ---------- Filtros (con debounce, filtran en el servidor) ---------- */

const filtros = reactive({
    search: props.filters.search ?? '',
    estado: props.filters.estado ?? '',
    periodo: props.filters.periodo ?? '',
    config: props.filters.config ?? '',
});

let timeout;

watch(filtros, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const params = Object.fromEntries(
            Object.entries(filtros).filter(([, v]) => v !== '' && v != null),
        );
        router.get('/cuotas', params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

onBeforeUnmount(() => clearTimeout(timeout));

/* ---------- Diálogos: se guarda el id para que se refresquen al recargar ---------- */

const pagoId = ref(null);
const pagosId = ref(null);

const cuotaPago = computed(() => props.cuotas.data.find((c) => c.id === pagoId.value) ?? null);
const cuotaPagos = computed(() => props.cuotas.data.find((c) => c.id === pagosId.value) ?? null);

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
</script>

<template>
    <Head title="Cuotas" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <!-- Encabezado -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Cuotas</h1>
                <p class="text-sm text-muted-foreground">
                    {{ cuotas.total }} {{ cuotas.total === 1 ? 'cuota' : 'cuotas' }}
                </p>
            </div>
            <Button as-child variant="outline">
                <Link href="/cuotas/configuraciones">
                    <Settings class="size-4" />
                    Configuración
                </Link>
            </Button>
        </div>

        <!-- Resumen -->
        <div class="grid gap-4 sm:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardDescription>Recaudado este mes</CardDescription>
                    <CardTitle class="text-2xl">{{ formatCLP(resumen.recaudado_mes) }}</CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>Cuotas vencidas</CardDescription>
                    <CardTitle class="text-2xl">{{ resumen.vencidas }}</CardTitle>
                </CardHeader>
            </Card>
        </div>

        <Card>
            <CardContent class="flex flex-col gap-4">
                <!-- Filtros -->
                <div class="flex flex-col gap-3 md:flex-row">
                    <div class="relative md:max-w-sm md:flex-1">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="filtros.search"
                            type="search"
                            placeholder="Buscar socio por RUT o nombre..."
                            class="pl-9"
                        />
                    </div>

                    <select v-model="filtros.estado" :class="selectClass">
                        <option value="">Todos los estados</option>
                        <option v-for="e in ESTADOS" :key="e" :value="e">{{ e }}</option>
                    </select>

                    <select v-model="filtros.periodo" :class="selectClass">
                        <option value="">Todos los períodos</option>
                        <option v-for="p in periodos" :key="p" :value="p">
                            {{ formatPeriodo(p) }}
                        </option>
                    </select>

                    <select v-model="filtros.config" :class="selectClass">
                        <option value="">Todas las cuotas</option>
                        <option v-for="c in configuraciones" :key="c.id" :value="String(c.id)">
                            {{ c.nombre }}
                        </option>
                    </select>
                </div>

                <!-- Tabla -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Socio</TableHead>
                                <TableHead>Cuota</TableHead>
                                <TableHead>Período</TableHead>
                                <TableHead class="text-right">Monto</TableHead>
                                <TableHead class="text-right">Pagado</TableHead>
                                <TableHead class="text-right">Saldo</TableHead>
                                <TableHead>Vence</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="c in cuotas.data" :key="c.id">
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">
                                            {{ c.socio?.persona?.full_name }}
                                        </span>
                                        <span class="font-mono text-xs text-muted-foreground">
                                            {{ c.socio?.persona?.rut }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>{{ c.configuracion?.nombre }}</TableCell>
                                <TableCell>{{ formatPeriodo(c.periodo) }}</TableCell>
                                <TableCell class="text-right">{{ formatCLP(c.monto_cuota) }}</TableCell>
                                <TableCell class="text-right">{{ formatCLP(c.pagado) }}</TableCell>
                                <TableCell class="text-right font-medium">{{ formatCLP(c.saldo) }}</TableCell>
                                <TableCell>{{ formatDate(c.fecha_vencimiento) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="VARIANTES[c.estado_efectivo] ?? 'secondary'">
                                        {{ c.estado_efectivo }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button
                                            v-if="c.saldo > 0"
                                            size="sm"
                                            @click="pagoId = c.id"
                                        >
                                            <Wallet class="size-4" />
                                            Registrar pago
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            title="Ver pagos"
                                            @click="pagosId = c.id"
                                        >
                                            <History class="size-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="cuotas.data.length === 0">
                                <TableCell colspan="9" class="h-32 text-center text-muted-foreground">
                                    No hay cuotas con esos filtros.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pie -->
                <div
                    v-if="cuotas.total > 0"
                    class="flex flex-col items-center justify-between gap-3 sm:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        Mostrando {{ cuotas.from }} a {{ cuotas.to }} de {{ cuotas.total }}
                    </p>
                    <Pagination :links="cuotas.links" />
                </div>
            </CardContent>
        </Card>
    </div>

    <RegistrarPagoDialog :cuota="cuotaPago" :medios="medios" @close="pagoId = null" />
    <PagosDialog :cuota="cuotaPagos" @close="pagosId = null" />
</template>
