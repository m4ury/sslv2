<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Download, Lock, LockOpen } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
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
import { useConfirm } from '@/composables/useConfirm';
import { usePermisos } from '@/composables/usePermisos';

const props = defineProps({
    configuracion: { type: Object, default: null },
    resumen: { type: Object, required: true },
    cierre: { type: Object, default: null },
    periodo: { type: String, required: true },
    periodos: { type: Array, default: () => [] },
    bloqueoCierre: { type: String, default: null },
    esUltimoCierre: { type: Boolean, default: false },
});

const { puede } = usePermisos();
const { confirmar } = useConfirm();

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

function cambiarPeriodo(evento) {
    router.get(`/caja/estado/${evento.target.value}`);
}

async function cerrarMes() {
    const ok = await confirmar({
        titulo: `¿Cerrar ${props.resumen.etiqueta}?`,
        descripcion:
            'Los totales quedarán definitivos y no se podrán registrar ni anular movimientos con fecha de ese mes.',
        confirmarTexto: 'Cerrar mes',
    });

    if (ok) {
        router.post(`/caja/estado/${props.periodo}/cerrar`, {}, { preserveScroll: true });
    }
}

async function reabrirMes() {
    const ok = await confirmar({
        titulo: `¿Reabrir ${props.resumen.etiqueta}?`,
        descripcion: 'Volverá a ser un estado provisorio y se podrán corregir movimientos de ese mes.',
        confirmarTexto: 'Reabrir',
        destructivo: true,
    });

    if (ok) {
        router.delete(`/caja/estado/${props.periodo}/cierre`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head :title="`Estado de cuentas ${resumen.etiqueta}`" />

    <div class="mx-auto flex max-w-6xl flex-col gap-6 p-4 md:p-6">
        <!-- Encabezado -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold">Estado de cuentas · {{ resumen.etiqueta }}</h1>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <Badge v-if="cierre">Mes cerrado</Badge>
                    <Badge v-else variant="outline">Provisorio (mes abierto)</Badge>
                    <span v-if="cierre" class="text-muted-foreground">
                        Cerrado por {{ cierre.cerrador?.name ?? '—' }} el
                        {{ formatDate(cierre.cerrado_at) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Button as-child variant="outline">
                    <Link href="/caja">
                        <ArrowLeft class="size-4" />
                        Volver a caja
                    </Link>
                </Button>

                <select v-if="periodos.length" :value="periodo" :class="selectClass" @change="cambiarPeriodo">
                    <option v-for="p in periodos" :key="p" :value="p">{{ formatPeriodo(p) }}</option>
                </select>

                <Button v-if="configuracion" as-child>
                    <a :href="`/caja/estado/${periodo}/pdf`">
                        <Download class="size-4" />
                        Descargar PDF
                    </a>
                </Button>
            </div>
        </div>

        <Card v-if="!configuracion">
            <CardHeader>
                <CardTitle>Caja sin configurar</CardTitle>
                <CardDescription>
                    Define el saldo inicial en la pantalla de caja para poder ver estados de cuentas.
                </CardDescription>
            </CardHeader>
        </Card>

        <template v-else>
            <!-- Cierre / reapertura -->
            <div v-if="puede('caja.cerrar')" class="flex flex-wrap items-center gap-3">
                <Button v-if="!cierre" :disabled="!!bloqueoCierre" @click="cerrarMes">
                    <Lock class="size-4" />
                    Cerrar mes
                </Button>
                <Button v-else-if="esUltimoCierre" variant="outline" @click="reabrirMes">
                    <LockOpen class="size-4" />
                    Reabrir mes
                </Button>
                <span v-if="!cierre && bloqueoCierre" class="text-sm text-muted-foreground">
                    {{ bloqueoCierre }}
                </span>
            </div>

            <!-- Totales -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader>
                        <CardDescription>Saldo inicial</CardDescription>
                        <CardTitle class="text-xl">{{ formatCLP(resumen.saldo_inicial) }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Ingresos</CardDescription>
                        <CardTitle class="text-xl">{{ formatCLP(resumen.total_ingresos) }}</CardTitle>
                        <p class="text-xs text-muted-foreground">
                            Cuotas {{ formatCLP(resumen.total_cuotas) }} · Otros
                            {{ formatCLP(resumen.total_otros) }}
                        </p>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Egresos</CardDescription>
                        <CardTitle class="text-xl">{{ formatCLP(resumen.total_egresos) }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Saldo final</CardDescription>
                        <CardTitle class="text-xl" :class="{ 'text-destructive': resumen.saldo_final < 0 }">
                            {{ formatCLP(resumen.saldo_final) }}
                        </CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <!-- Ingresos por cuotas -->
            <Card>
                <CardHeader>
                    <CardTitle>Ingresos por cuotas</CardTitle>
                    <CardDescription>
                        {{ resumen.cuotas.length }} pagos · {{ formatCLP(resumen.total_cuotas) }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Socio</TableHead>
                                    <TableHead>Cuota</TableHead>
                                    <TableHead>Medio</TableHead>
                                    <TableHead>Comprobante</TableHead>
                                    <TableHead class="text-right">Monto</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="c in resumen.cuotas" :key="c.id">
                                    <TableCell>{{ formatDate(c.fecha_pago) }}</TableCell>
                                    <TableCell>
                                        {{ c.socio }}
                                        <span class="block font-mono text-xs text-muted-foreground">{{ c.rut }}</span>
                                    </TableCell>
                                    <TableCell>{{ formatPeriodo(c.periodo) }}</TableCell>
                                    <TableCell>{{ c.medio_pago }}</TableCell>
                                    <TableCell>{{ c.numero_comprobante ?? '—' }}</TableCell>
                                    <TableCell class="text-right">{{ formatCLP(c.monto_cuotas) }}</TableCell>
                                </TableRow>
                                <TableRow v-if="resumen.cuotas.length === 0">
                                    <TableCell colspan="6" class="h-16 text-center text-muted-foreground">
                                        Sin pagos de cuotas en el mes.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Otros ingresos -->
            <Card>
                <CardHeader>
                    <CardTitle>Otros ingresos</CardTitle>
                    <CardDescription>
                        {{ resumen.otros.length }} movimientos · {{ formatCLP(resumen.total_otros) }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div v-if="resumen.otros_por_categoria.length" class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Categoría</TableHead>
                                    <TableHead class="text-right">Movimientos</TableHead>
                                    <TableHead class="text-right">Total</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="c in resumen.otros_por_categoria" :key="c.categoria">
                                    <TableCell>{{ c.categoria }}</TableCell>
                                    <TableCell class="text-right">{{ c.cantidad }}</TableCell>
                                    <TableCell class="text-right">{{ formatCLP(c.total) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
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
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="o in resumen.otros" :key="o.id">
                                    <TableCell>{{ formatDate(o.fecha) }}</TableCell>
                                    <TableCell>{{ o.categoria }}</TableCell>
                                    <TableCell>
                                        <div class="flex max-w-xs flex-col whitespace-normal">
                                            <span>{{ o.descripcion }}</span>
                                            <span v-if="o.origen" class="text-xs text-muted-foreground">{{ o.origen }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ o.documento || '—' }}</TableCell>
                                    <TableCell class="text-right">{{ formatCLP(o.monto) }}</TableCell>
                                </TableRow>
                                <TableRow v-if="resumen.otros.length === 0">
                                    <TableCell colspan="5" class="h-16 text-center text-muted-foreground">
                                        Sin otros ingresos en el mes.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Egresos -->
            <Card>
                <CardHeader>
                    <CardTitle>Egresos</CardTitle>
                    <CardDescription>
                        {{ resumen.egresos.length }} movimientos · {{ formatCLP(resumen.total_egresos) }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div v-if="resumen.egresos_por_categoria.length" class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Categoría</TableHead>
                                    <TableHead class="text-right">Movimientos</TableHead>
                                    <TableHead class="text-right">Total</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="c in resumen.egresos_por_categoria" :key="c.categoria">
                                    <TableCell>{{ c.categoria }}</TableCell>
                                    <TableCell class="text-right">{{ c.cantidad }}</TableCell>
                                    <TableCell class="text-right">{{ formatCLP(c.monto_egreso) }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
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
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="e in resumen.egresos" :key="e.id">
                                    <TableCell>{{ formatDate(e.fecha_egreso) }}</TableCell>
                                    <TableCell>{{ e.categoria }}</TableCell>
                                    <TableCell>
                                        <div class="flex max-w-xs flex-col whitespace-normal">
                                            <span>{{ e.descripcion }}</span>
                                            <span v-if="e.proveedor" class="text-xs text-muted-foreground">
                                                {{ e.proveedor }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ e.documento || '—' }}</TableCell>
                                    <TableCell class="text-right">{{ formatCLP(e.monto_egresos) }}</TableCell>
                                </TableRow>
                                <TableRow v-if="resumen.egresos.length === 0">
                                    <TableCell colspan="5" class="h-16 text-center text-muted-foreground">
                                        Sin egresos en el mes.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </template>
    </div>
</template>
