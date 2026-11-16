<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import ArticuloDialog from '@/components/inventario/ArticuloDialog.vue';
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
import { usePermisos } from '@/composables/usePermisos';

const props = defineProps({
    articulos: { type: Array, required: true },
    categorias: { type: Array, default: () => [] },
    resumen: { type: Object, required: true },
});

const { puede } = usePermisos();

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

/* ---------- Filtros (en el navegador: el catálogo es chico) ---------- */

const busqueda = ref('');
const categoria = ref('');
const vista = ref('activos');

const normalizar = (texto) =>
    String(texto ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

const lista = computed(() => {
    const q = normalizar(busqueda.value);

    return props.articulos.filter((a) => {
        if (vista.value === 'activos' && !a.activo) return false;
        if (vista.value === 'inactivos' && a.activo) return false;
        if (vista.value === 'bajo' && !a.bajo_stock) return false;
        if (vista.value === 'prestamo' && a.en_prestamo === 0) return false;
        if (categoria.value && a.categoria !== categoria.value) return false;

        return !q || normalizar(`${a.codigo} ${a.nombre} ${a.ubicacion}`).includes(q);
    });
});

const creando = ref(false);
</script>

<template>
    <Head title="Inventario" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Inventario</h1>
                <p class="text-sm text-muted-foreground">
                    Artículos del centro, su stock y lo que está en préstamo.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button as-child variant="outline">
                    <Link href="/inventario/prestamos">Préstamos</Link>
                </Button>
                <Button v-if="puede('inventario.articulos')" @click="creando = true">
                    <Plus class="size-4" />
                    Nuevo artículo
                </Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader>
                    <CardDescription>Artículos activos</CardDescription>
                    <CardTitle class="text-2xl">{{ resumen.activos }}</CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>Con unidades en préstamo</CardDescription>
                    <CardTitle class="text-2xl">{{ resumen.con_prestamo }}</CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>Préstamos vencidos</CardDescription>
                    <CardTitle class="text-2xl" :class="{ 'text-destructive': resumen.vencidos > 0 }">
                        {{ resumen.vencidos }}
                    </CardTitle>
                </CardHeader>
            </Card>
            <Card>
                <CardHeader>
                    <CardDescription>Bajo el stock mínimo</CardDescription>
                    <CardTitle class="text-2xl" :class="{ 'text-destructive': resumen.bajo_stock > 0 }">
                        {{ resumen.bajo_stock }}
                    </CardTitle>
                </CardHeader>
            </Card>
        </div>

        <Card>
            <CardContent class="flex flex-col gap-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <Input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar por código, nombre o ubicación..."
                        class="lg:max-w-sm lg:flex-1"
                    />

                    <select v-model="categoria" :class="selectClass">
                        <option value="">Todas las categorías</option>
                        <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                    </select>

                    <select v-model="vista" :class="selectClass">
                        <option value="activos">Activos</option>
                        <option value="bajo">Bajo stock mínimo</option>
                        <option value="prestamo">Con préstamos</option>
                        <option value="inactivos">Inactivos</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Código</TableHead>
                                <TableHead>Artículo</TableHead>
                                <TableHead>Categoría</TableHead>
                                <TableHead class="text-right">Existencia</TableHead>
                                <TableHead class="text-right">Disponible</TableHead>
                                <TableHead class="text-right">En préstamo</TableHead>
                                <TableHead>Estado</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="a in lista" :key="a.id">
                                <TableCell class="font-mono text-xs">{{ a.codigo }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <Link
                                            :href="`/inventario/${a.id}`"
                                            class="font-medium underline-offset-4 hover:underline"
                                        >
                                            {{ a.nombre }}
                                        </Link>
                                        <span v-if="a.ubicacion" class="text-xs text-muted-foreground">
                                            {{ a.ubicacion }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>{{ a.categoria }}</TableCell>
                                <TableCell class="text-right">{{ a.existencia }} {{ a.unidad }}</TableCell>
                                <TableCell class="text-right font-medium">{{ a.disponible }}</TableCell>
                                <TableCell class="text-right">{{ a.en_prestamo }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-if="!a.activo" variant="outline">Inactivo</Badge>
                                        <Badge v-if="a.bajo_stock" variant="destructive">Bajo stock</Badge>
                                        <Badge v-if="a.activo && !a.bajo_stock" variant="secondary">Normal</Badge>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="lista.length === 0">
                                <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
                                    No hay artículos con esos filtros.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>

    <ArticuloDialog v-model:open="creando" :categorias="categorias" />
</template>
