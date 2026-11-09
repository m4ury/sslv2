<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { onBeforeUnmount, reactive, watch } from 'vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
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
import { formatCLP, formatDate } from '@/config/ficha';

const props = defineProps({
    aportes: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    medios: { type: Array, default: () => [] },
    resumen: { type: Object, required: true },
});

const filtros = reactive({
    search: props.filters.search ?? '',
    medio: props.filters.medio ?? '',
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
        router.get('/aportes', params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

onBeforeUnmount(() => clearTimeout(timeout));

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
</script>

<template>
    <Head title="Aportes" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold">Aportes voluntarios</h1>
            <p class="text-sm text-muted-foreground">
                Aportes entregados por los colaboradores de la agrupación.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardDescription>Total aportado (según filtros)</CardDescription>
                    <CardTitle class="text-2xl">{{ formatCLP(resumen.total) }}</CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>Cantidad de aportes vigentes</CardDescription>
                    <CardTitle class="text-2xl">{{ resumen.cantidad }}</CardTitle>
                </CardHeader>
            </Card>
        </div>

        <Card>
            <CardContent class="flex flex-col gap-4">
                <!-- Filtros -->
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="relative lg:max-w-sm lg:flex-1">
                        <Search
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="filtros.search"
                            type="search"
                            placeholder="Buscar colaborador por RUT o nombre..."
                            class="pl-9"
                        />
                    </div>

                    <select v-model="filtros.medio" :class="selectClass">
                        <option value="">Todos los medios</option>
                        <option v-for="m in medios" :key="m" :value="m">{{ m }}</option>
                    </select>

                    <select v-model="filtros.estado" :class="selectClass">
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

                <!-- Tabla -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Fecha</TableHead>
                                <TableHead>Colaborador</TableHead>
                                <TableHead class="text-right">Monto</TableHead>
                                <TableHead>Medio</TableHead>
                                <TableHead>Comprobante</TableHead>
                                <TableHead>Registrado por</TableHead>
                                <TableHead>Estado</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="a in aportes.data"
                                :key="a.id"
                                :class="{ 'opacity-60': a.anulado_at }"
                            >
                                <TableCell>{{ formatDate(a.fecha_aporte) }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <Link
                                            :href="`/colaboradores/${a.colaborador_id}/edit`"
                                            class="font-medium underline-offset-4 hover:underline"
                                        >
                                            {{ a.colaborador?.persona?.full_name }}
                                        </Link>
                                        <span class="font-mono text-xs text-muted-foreground">
                                            {{ a.colaborador?.persona?.rut }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell
                                    class="text-right font-medium"
                                    :class="{ 'line-through': a.anulado_at }"
                                >
                                    {{ formatCLP(a.monto) }}
                                </TableCell>
                                <TableCell>{{ a.medio_pago ?? '—' }}</TableCell>
                                <TableCell>{{ a.numero_comprobante ?? '—' }}</TableCell>
                                <TableCell>{{ a.usuario?.name ?? '—' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="a.anulado_at ? 'destructive' : 'secondary'">
                                        {{ a.anulado_at ? 'Anulado' : 'Vigente' }}
                                    </Badge>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="aportes.data.length === 0">
                                <TableCell colspan="7" class="h-32 text-center text-muted-foreground">
                                    No hay aportes con esos filtros.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pie -->
                <div
                    v-if="aportes.total > 0"
                    class="flex flex-col items-center justify-between gap-3 sm:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        Mostrando {{ aportes.from }} a {{ aportes.to }} de {{ aportes.total }}
                    </p>
                    <Pagination :links="aportes.links" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>
