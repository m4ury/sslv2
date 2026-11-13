<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Download } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
    titulo: { type: String, required: true },
    periodo: { type: String, required: true },
    etiqueta: { type: String, required: true },
    periodos: { type: Array, default: () => [] },
    columnas: { type: Array, required: true },
    filas: { type: Array, default: () => [] },
    cantidad: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
    agrupaciones: { type: Array, default: () => [] },
    baseUrl: { type: String, required: true },
    volverA: { type: String, default: '/' },
});

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

function cambiarPeriodo(evento) {
    router.get(`${props.baseUrl}/${evento.target.value}`);
}

function celda(col, fila) {
    const valor = fila[col.key];
    if (valor == null || valor === '') return '—';
    if (col.tipo === 'fecha') return formatDate(valor);
    if (col.tipo === 'dinero') return formatCLP(valor);
    return valor;
}
</script>

<template>
    <Head :title="`${titulo} ${etiqueta}`" />

    <div class="mx-auto flex max-w-6xl flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ titulo }}</h1>
                <p class="text-sm text-muted-foreground">{{ etiqueta }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Button as-child variant="outline">
                    <Link :href="volverA">
                        <ArrowLeft class="size-4" />
                        Volver
                    </Link>
                </Button>

                <select :value="periodo" :class="selectClass" @change="cambiarPeriodo">
                    <option v-for="p in periodos" :key="p" :value="p">{{ formatPeriodo(p) }}</option>
                </select>

                <Button as-child>
                    <a :href="`${baseUrl}/${periodo}/pdf`">
                        <Download class="size-4" />
                        Descargar PDF
                    </a>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardDescription>Movimientos del mes</CardDescription>
                    <CardTitle class="text-2xl">{{ cantidad }}</CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>Total del mes</CardDescription>
                    <CardTitle class="text-2xl">{{ formatCLP(total) }}</CardTitle>
                </CardHeader>
            </Card>
        </div>

        <Card v-for="a in agrupaciones" :key="a.titulo">
            <CardHeader>
                <CardTitle>{{ a.titulo }}</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead class="text-right">Movimientos</TableHead>
                                <TableHead class="text-right">Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="f in a.filas" :key="f.nombre">
                                <TableCell>{{ f.nombre }}</TableCell>
                                <TableCell class="text-right">{{ f.cantidad }}</TableCell>
                                <TableCell class="text-right">{{ formatCLP(f.total) }}</TableCell>
                            </TableRow>
                            <TableRow v-if="a.filas.length === 0">
                                <TableCell colspan="3" class="h-16 text-center text-muted-foreground">
                                    Sin movimientos en el mes.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Detalle</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead
                                    v-for="col in columnas"
                                    :key="col.key"
                                    :class="{ 'text-right': col.tipo === 'dinero' }"
                                >
                                    {{ col.label }}
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(fila, i) in filas" :key="i">
                                <TableCell
                                    v-for="col in columnas"
                                    :key="col.key"
                                    :class="{ 'text-right': col.tipo === 'dinero' }"
                                >
                                    {{ celda(col, fila) }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="filas.length === 0">
                                <TableCell :colspan="columnas.length" class="h-16 text-center text-muted-foreground">
                                    Sin movimientos en el mes.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>