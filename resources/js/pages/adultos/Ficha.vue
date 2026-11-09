<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, Pencil } from '@lucide/vue';
import { computed } from 'vue';
import FichaSeccion from '@/components/ficha/FichaSeccion.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    crearSecciones,
    estadoVigencia,
    fechaLocal,
    formatDate,
} from '@/config/ficha';

import { usePermisos } from '@/composables/usePermisos';

const { puede } = usePermisos();

const props = defineProps({
    adulto: { type: Object, required: true },
    persona: { type: Object, required: true },
    patologias: { type: Array, default: () => [] },
    controles: { type: Array, default: () => [] },
    prestaciones: { type: Array, default: () => [] },
    programados: { type: Array, default: () => [] },
    credenciales: { type: Array, default: () => [] },
    documentos: { type: Array, default: () => [] },
    catalogos: { type: Object, required: true },
    catalogoPatologias: { type: Array, default: () => [] },
    cuidadores: { type: Array, default: () => [] },
    catalogoCuidadores: { type: Array, default: () => [] },
});

const base = computed(() => `/adultos/${props.adulto.id}/ficha`);

const fullName = computed(
    () =>
        props.persona.full_name ??
        [
            props.persona.nombres,
            props.persona.apellido_paterno,
            props.persona.apellido_materno,
        ]
            .filter(Boolean)
            .join(' '),
);

const secciones = computed(() =>
    crearSecciones(
        {
             catalogos: props.catalogos,
             controles: props.controles,
             catalogoPatologias: props.catalogoPatologias,
             catalogoCuidadores: props.catalogoCuidadores,
        }),
);

// Cada medición del control se aplana como med_<tipo> para el formulario genérico
const controlesPlanos = computed(() =>
    props.controles.map((c) => ({
        ...c,
        ...Object.fromEntries(
            (c.mediciones ?? []).map((m) => [`med_${m.tipo}`, m.valor]),
        ),
    })),
);
const cuidadoresPlanos = computed(() =>
    props.cuidadores.map((c) => ({ ...c, es_principal: c.es_principal ? 1 : 0 })),
);

const cuidadorPrincipal = computed(() =>
    props.cuidadores.find((c) => c.es_principal && !c.fecha_fin) ?? null,
);

/* ---------- Resumen ---------- */

const patologiasActivas = computed(() =>
    props.patologias.filter((p) => p.estado === 'Activa'),
);

const ultimoControl = computed(() => props.controles[0] ?? null);

const pendientes = computed(() =>
    props.programados.filter((p) => p.estado === 'Pendiente'),
);

const proximoControl = computed(() => pendientes.value[0] ?? null);

const atrasados = computed(
    () =>
        pendientes.value.filter(
            (p) => String(p.fecha_programada).substring(0, 10) < fechaLocal(),
        ).length,
);

const credencialesAlerta = computed(() =>
    props.credenciales.filter((c) =>
        ['Vencida', 'Por vencer'].includes(estadoVigencia(c.fecha_expiracion)),
    ),
);

// Última medición registrada de cada tipo (los controles vienen ordenados por fecha desc)
function buscarMedicion(tipo) {
    for (const control of props.controles) {
        const medicion = control.mediciones?.find((m) => m.tipo === tipo);
        if (medicion) return { medicion, control };
    }
    return null;
}

const ultimasMediciones = computed(() => {
    const items = [];

    const pa = buscarMedicion('pa_sistolica');
    if (pa) {
        const dia = pa.control.mediciones.find((m) => m.tipo === 'pa_diastolica');
        items.push({
            label: 'Presión arterial',
            valor: `${pa.medicion.valor}/${dia?.valor ?? '?'}`,
            unidad: 'mmHg',
            fecha: pa.control.fecha,
        });
    }

    for (const [tipo, def] of Object.entries(props.catalogos.mediciones)) {
        if (tipo.startsWith('pa_')) continue;
        const r = buscarMedicion(tipo);
        if (r) {
            items.push({
                label: def.label,
                valor: r.medicion.valor,
                unidad: def.unidad,
                fecha: r.control.fecha,
            });
        }
    }

    const peso = buscarMedicion('peso');
    const talla = buscarMedicion('talla');
    if (peso && talla) {
        const m = talla.medicion.valor / 100;
        items.push({
            label: 'IMC',
            valor: (peso.medicion.valor / (m * m)).toFixed(1),
            unidad: 'kg/m²',
            fecha: peso.control.fecha,
        });
    }

    return items;
});
</script>

<template>
    <Head :title="`Ficha clínica · ${fullName}`" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <!-- Encabezado -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold">{{ fullName }}</h1>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="font-mono text-muted-foreground">
                        {{ persona.rut }}
                    </span>
                    <span v-if="persona.edad != null" class="text-muted-foreground">
                        · {{ persona.edad }} años
                    </span>
                    <Badge :variant="adulto.estado === 'Activo' ? 'default' : 'secondary'">
                        {{ adulto.estado }}
                    </Badge>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button as-child variant="outline">
                    <Link :href="`/personas/${persona.id}`">
                        <ArrowLeft class="size-4" />
                        Volver a la persona
                    </Link>
                </Button>
                <Button as-child variant="outline">
                    <Link :href="`/adultos/${adulto.id}/edit`">
                        <Pencil class="size-4" />
                        Editar datos del adulto
                    </Link>
                </Button>
            </div>
        </div>

        <Tabs default-value="resumen" class="min-w-0 gap-4">
            <TabsList
                class="h-auto min-h-9 w-full flex-wrap content-start items-start justify-start gap-1"
            >
                <TabsTrigger value="resumen" class="h-9 flex-none">Resumen</TabsTrigger>
                <TabsTrigger value="patologias" class="h-9 flex-none">
                    Patologías ({{ patologias.length }})
                </TabsTrigger>
                <TabsTrigger value="controles" class="h-9 flex-none">
                    Controles ({{ controles.length }})
                </TabsTrigger>
                <TabsTrigger value="prestaciones" class="h-9 flex-none">
                    Prestaciones ({{ prestaciones.length }})
                </TabsTrigger>
                <TabsTrigger value="programados" class="h-9 flex-none">
                    Programados ({{ pendientes.length }})
                </TabsTrigger>
                <TabsTrigger value="credenciales" class="h-9 flex-none">
                    Credenciales ({{ credenciales.length }})
                </TabsTrigger>
                <TabsTrigger value="documentos" class="h-9 flex-none">
                    Documentos ({{ documentos.length }})
                </TabsTrigger>
                <TabsTrigger value="cuidadores" class="h-9 flex-none">
                    Cuidadores ({{ cuidadores.filter((c) => !c.fecha_fin).length }})
                </TabsTrigger>
            </TabsList>

            <!-- Resumen -->
            <TabsContent value="resumen" class="flex flex-col gap-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Patologías activas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="patologiasActivas.length"
                                class="flex flex-wrap gap-2"
                            >
                                <Badge    v-for="p in patologiasActivas"
                                         :key="p.id"
                                          variant="secondary"
                                >
                                    {{ p.patologia?.nombre }}
                                </Badge>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">
                                Sin patologías activas registradas.
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Seguimiento</CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-muted-foreground">Último control</span>
                                <span>
                                    {{
                                        ultimoControl
                                            ? `${formatDate(ultimoControl.fecha)} · ${ultimoControl.tipo}`
                                            : '—'
                                    }}
                                </span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-muted-foreground">Próximo control</span>
                                <span>
                                    {{
                                        proximoControl
                                            ? `${formatDate(proximoControl.fecha_programada)} · ${proximoControl.tipo}`
                                            : '—'
                                    }}
                                </span>
                            </div>
                            <div
                                v-if="atrasados > 0"
                                class="flex justify-between gap-4"
                            >
                                <span class="text-muted-foreground">Controles atrasados</span>
                                <Badge variant="destructive">{{ atrasados }}</Badge>
                            </div>
                            <div
                                v-if="credencialesAlerta.length"
                                class="flex justify-between gap-4"
                            >
                                <span class="text-muted-foreground">Credenciales por expirar</span>
                                <Badge variant="destructive">
                                    {{ credencialesAlerta.length }}
                                </Badge>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-muted-foreground">Cuidador principal</span>
                                <span>
                                    {{
                                        cuidadorPrincipal
                                            ? `${cuidadorPrincipal.cuidador?.persona?.full_name} · ${cuidadorPrincipal.cuidador?.telefono}`
                                            : '—'
                                    }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Últimas mediciones</CardTitle>
                        <CardDescription>
                            Último valor registrado de cada medición.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="ultimasMediciones.length"
                            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <div
                                v-for="m in ultimasMediciones"
                                :key="m.label"
                                class="flex flex-col gap-1 rounded-lg border p-4"
                            >
                                <span class="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    {{ m.label }}
                                </span>
                                <span class="text-xl font-semibold">
                                    {{ m.valor }}
                                    <span class="text-sm font-normal text-muted-foreground">
                                        {{ m.unidad }}
                                    </span>
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatDate(m.fecha) }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            Aún no hay mediciones registradas.
                        </p>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="patologias" class="flex flex-col gap-3">
                <FichaSeccion
                    :seccion="secciones.patologias"
                    :items="patologias"
                    :url="`${base}/patologias`"
                />
                <p class="text-sm text-muted-foreground">
                    ¿No encuentras la patología?
                    <Link
                        :href="`/catalogos/patologias?adulto=${adulto.id}`"
                        class="underline underline-offset-4"
                    >
                        Agrégala al catálogo
                    </Link>
                </p>
            </TabsContent>

            <TabsContent value="controles">
                <FichaSeccion
                    :seccion="secciones.controles"
                    :items="controlesPlanos"
                    :url="`${base}/controles`"
                />
            </TabsContent>

            <TabsContent value="prestaciones">
                <FichaSeccion
                    :seccion="secciones.prestaciones"
                    :items="prestaciones"
                    :url="`${base}/prestaciones`"
                />
            </TabsContent>

            <TabsContent value="programados">
                <FichaSeccion
                    :seccion="secciones.programados"
                    :items="programados"
                    :url="`${base}/programados`"
                />
            </TabsContent>

            <TabsContent value="credenciales">
                <FichaSeccion
                    :seccion="secciones.credenciales"
                    :items="credenciales"
                    :url="`${base}/credenciales`"
                />
            </TabsContent>

            <TabsContent value="documentos">
                <FichaSeccion
                    :seccion="secciones.documentos"
                    :items="documentos"
                    :url="`${base}/documentos`"
                >
                    <template #acciones="{ item }">
                        <Button as-child variant="ghost" size="icon" title="Descargar">
                            <a :href="`${base}/documentos/${item.id}/descargar`">
                                <Download class="size-4" />
                            </a>
                        </Button>
                    </template>
                </FichaSeccion>
            </TabsContent>
            <TabsContent value="cuidadores" class="flex flex-col gap-3">
                <FichaSeccion
                    :seccion="secciones.cuidadores"
                    :items="cuidadoresPlanos"
                    :url="`${base}/cuidadores`"
                />
                <p v-if="puede('patologias.catalogo')" class="text-sm text-muted-foreground">
                    ¿No aparece el cuidador? Primero debe estar registrado: ábrelo en
                    <Link href="/personas" class="underline underline-offset-4">Personas</Link>
                    y usa "Registrar como cuidador".
                </p>
            </TabsContent>
        </Tabs>
    </div>
</template>
