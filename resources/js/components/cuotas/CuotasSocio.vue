<script setup>
import { History, Wallet } from '@lucide/vue';
import { computed, ref } from 'vue';
import PagosDialog from '@/components/cuotas/PagosDialog.vue';
import RegistrarPagoDialog from '@/components/cuotas/RegistrarPagoDialog.vue';
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCLP, formatDate, formatPeriodo } from '@/config/ficha';

const props = defineProps({
    cuotas: { type: Array, default: () => [] },
    medios: { type: Array, default: () => [] },
});

const VARIANTES = {
    Pagada: 'secondary',
    Pendiente: 'outline',
    Parcial: 'default',
    Vencida: 'destructive',
};

// Se guarda el id para que los diálogos se refresquen al recargar la página
const pagoId = ref(null);
const pagosId = ref(null);

const cuotaPago = computed(() => props.cuotas.find((c) => c.id === pagoId.value) ?? null);
const cuotaPagos = computed(() => props.cuotas.find((c) => c.id === pagosId.value) ?? null);

const deuda = computed(() =>
    props.cuotas
        .filter((c) => c.estado !== 'Pagada')
        .reduce((total, c) => total + c.saldo, 0),
);

const vencidas = computed(
    () => props.cuotas.filter((c) => c.estado_efectivo === 'Vencida').length,
);
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-start justify-between gap-4">
            <div class="flex flex-col gap-1.5">
                <CardTitle>Cuotas</CardTitle>
                <CardDescription>
                    Deuda pendiente:
                    <span class="font-medium text-foreground">{{ formatCLP(deuda) }}</span>
                    <template v-if="vencidas > 0">
                        · {{ vencidas }} {{ vencidas === 1 ? 'vencida' : 'vencidas' }}
                    </template>
                </CardDescription>
            </div>
        </CardHeader>

        <CardContent>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
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
                        <TableRow v-for="c in cuotas" :key="c.id">
                            <TableCell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ formatPeriodo(c.periodo) }}</span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ c.configuracion?.nombre }}
                                    </span>
                                </div>
                            </TableCell>
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
                                    <Button v-if="c.saldo > 0" size="sm" @click="pagoId = c.id">
                                        <Wallet class="size-4" />
                                        Pagar
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

                        <TableRow v-if="cuotas.length === 0">
                            <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
                                Este socio aún no tiene cuotas generadas.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </CardContent>
    </Card>

    <RegistrarPagoDialog :cuota="cuotaPago" :medios="medios" @close="pagoId = null" />
    <PagosDialog :cuota="cuotaPagos" @close="pagosId = null" />
</template>
