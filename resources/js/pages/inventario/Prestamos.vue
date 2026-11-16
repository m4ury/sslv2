<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { computed, ref } from 'vue';
import Pagination from '@/components/Pagination.vue';
import MovimientoDialog from '@/components/inventario/MovimientoDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useFiltros } from '@/composables/useFiltros';
import { usePermisos } from '@/composables/usePermisos';
import { formatDate } from '@/config/ficha';

const props = defineProps({
    prestamos: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    opciones: { type: Object, required: true },
});

const { puede } = usePermisos();

const ESTADOS_PRESTAMO = { Vigente: 'default', Vencido: 'destructive', Devuelto: 'secondary' };

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

const filtros = useFiltros('/inventario/prestamos', {
    search: props.filters.search ?? '',
    estado: props.filters.estado ?? 'Pendientes',
});

/* ---------- Devolución ---------- */

const prestamoId = ref(null);
const prestamoSel = computed(() => props.prestamos.data.find((p) => p.id === prestamoId.value) ?? null);
</script>

<template>
    <Head title="Préstamos de inventario" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Préstamos</h1>
                <p class="text-sm text-muted-foreground">Artículos que salieron del centro de forma transitoria.</p>
            </div>
            <Button as-child variant="outline">
                <Link href="/inventario">
                    <ArrowLeft class="size-4" />
                    Volver al inventario
                </Link>
            </Button>
        </div>

        <Card>
            <CardContent class="flex flex-col gap-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <Input
                        v-model="filtros.search"
                        type="search"
                        placeholder="Buscar por prestatario o artículo..."
                        class="lg:max-w-sm lg:flex-1"
                    />
                    <select v-model="filtros.estado" :class="selectClass">
                        <option value="Pendientes">Pendientes de devolución</option>
                        <option value="Vencido">Vencidos</option>
                        <option value="Devuelto">Devueltos</option>
                        <option value="Todos">Todos</option>
                    </select>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Artículo</TableHead>
                                <TableHead>Prestado a</TableHead>
                                <TableHead class="text-right">Cantidad</TableHead>
                                <TableHead class="text-right">Pendiente</TableHead>
                                <TableHead>Desde</TableHead>
                                <TableHead>Devolver el</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="p in prestamos.data" :key="p.id">
                                <TableCell>
                                    <Link
                                        :href="`/inventario/${p.articulo.id}`"
                                        class="font-medium underline-offset-4 hover:underline"
                                    >
                                        {{ p.articulo.nombre }}
                                    </Link>
                                    <span class="block font-mono text-xs text-muted-foreground">
                                        {{ p.articulo.codigo }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    {{ p.prestatario }}
                                    <span v-if="p.telefono" class="block text-xs text-muted-foreground">
                                        {{ p.telefono }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">{{ p.cantidad }}</TableCell>
                                <TableCell class="text-right font-medium">{{ p.pendiente }}</TableCell>
                                <TableCell>{{ formatDate(p.fecha) }}</TableCell>
                                <TableCell>{{ p.fecha_compromiso ? formatDate(p.fecha_compromiso) : '—' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="ESTADOS_PRESTAMO[p.estado]">{{ p.estado }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex justify-end">
                                        <Button
                                            v-if="p.pendiente > 0 && puede('inventario.registrar')"
                                            size="sm"
                                            @click="prestamoId = p.id"
                                        >
                                            Registrar devolución
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="prestamos.data.length === 0">
                                <TableCell colspan="8" class="h-24 text-center text-muted-foreground">
                                    No hay préstamos con esos filtros.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div
                    v-if="prestamos.total > 0"
                    class="flex flex-col items-center justify-between gap-3 sm:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        Mostrando {{ prestamos.from }} a {{ prestamos.to }} de {{ prestamos.total }}
                    </p>
                    <Pagination :links="prestamos.links" />
                </div>
            </CardContent>
        </Card>
    </div>

    <MovimientoDialog
        :articulo="prestamoSel?.articulo ?? null"
        :tipo="prestamoSel ? 'devolucion' : null"
        :prestamo="prestamoSel"
        :opciones="opciones"
        @close="prestamoId = null"
    />
</template>
