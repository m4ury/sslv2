<script setup>
import esLocale from '@fullcalendar/vue3/locales/es';
import classicThemePlugin from '@fullcalendar/vue3/themes/classic';
import dayGridPlugin from '@fullcalendar/vue3/daygrid';
import interactionPlugin from '@fullcalendar/vue3/interaction';
import timeGridPlugin from '@fullcalendar/vue3/timegrid';
import FullCalendar from '@fullcalendar/vue3';
import '@fullcalendar/vue3/skeleton.css';
import '@fullcalendar/vue3/themes/classic/theme.css';
import '@fullcalendar/vue3/themes/classic/palette.css';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, reactive, ref, watch } from 'vue';
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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { usePermisos } from '@/composables/usePermisos';
import { formatDate } from '@/config/ficha';

const props = defineProps({
    eventos: { type: Array, default: () => [] },
    rango: { type: Object, required: true },
    resumen: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    estados: { type: Array, default: () => [] },
    tipos: { type: Array, default: () => [] },
});

const { puede } = usePermisos();

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

const PUNTOS = {
    Pendiente: 'bg-primary',
    Realizado: 'bg-emerald-600',
    'No asistió': 'bg-amber-500',
    Cancelado: 'bg-destructive',
};

/* ---------- Utilidades ---------- */

// "No asistió" → "no-asistio" (clase CSS del bloque)
const slug = (texto) =>
    texto
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '-');

function horaFin(hora, duracion) {
    const [h, m] = hora.split(':').map(Number);
    const total = h * 60 + m + duracion;
    const hh = String(Math.floor(total / 60) % 24).padStart(2, '0');
    const mm = String(total % 60).padStart(2, '0');
    return `${hh}:${mm}`;
}

function textoHora(ep) {
    return ep.hora
        ? `${ep.hora} – ${horaFin(ep.hora, ep.duracion)}`
        : 'Sin hora asignada';
}

/* ---------- Carga por rango y filtros ---------- */

const filtros = reactive({
    estado: props.filters.estado ?? '',
    tipo: props.filters.tipo ?? '',
});

function cargar(rango) {
    const params = { desde: rango.desde, hasta: rango.hasta };
    if (filtros.estado) params.estado = filtros.estado;
    if (filtros.tipo) params.tipo = filtros.tipo;

    router.get('/agenda', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['eventos', 'rango', 'resumen', 'filters'],
    });
}

watch(filtros, () => cargar(props.rango));

// FullCalendar avisa cada vez que cambia el rango visible (end es exclusivo)
function alCambiarRango(arg) {
    const desde = arg.start.toLocaleDateString('en-CA');
    const ultimo = new Date(arg.end);
    ultimo.setDate(ultimo.getDate() - 1);
    const hasta = ultimo.toLocaleDateString('en-CA');

    if (desde === props.rango.desde && hasta === props.rango.hasta) return;

    cargar({ desde, hasta });
}

/* ---------- Detalle al hacer clic ---------- */

const seleccionado = ref(null);
const calendario = ref(null);

onMounted(() => {
    if (window.innerWidth < 768) {
        calendario.value?.getApi().changeView('timeGridDay');
    }
});

/* ---------- Calendario ---------- */

const opciones = reactive({
    plugins: [classicThemePlugin, dayGridPlugin, timeGridPlugin, interactionPlugin],
    locale: esLocale,
    initialView: 'timeGridWeek',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    firstDay: 1,
    slotMinTime: '07:00:00',
    slotMaxTime: '21:00:00',
    slotDuration: '00:30:00',
    slotHeaderFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    allDayText: 'Sin hora',
    nowIndicator: true,
    navLinks: true,
    height: 'auto',
    events: props.eventos,
    eventClass: (arg) => `evento-${slug(arg.event.extendedProps.estado)}`,
    eventClick: (info) => {
        seleccionado.value = {
            titulo: info.event.title,
            fecha: info.event.startStr.substring(0, 10),
            ...info.event.extendedProps,
        };
    },
    datesSet: alCambiarRango,
});

watch(
    () => props.eventos,
    (nuevos) => {
        opciones.events = nuevos;
    },
);
</script>

<template>
    <div>
        <Head title="Agenda" />

        <div class="agenda mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">Agenda de controles</h1>
                <p class="text-sm text-muted-foreground">
                    Controles programados de los adultos, por fecha y hora. Pasa
                    el cursor sobre un bloque para ver de quién es.
                </p>
            </div>

            <!-- Carga de trabajo del período visible -->
            <div class="grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardDescription
                            >Controles en el período</CardDescription
                        >
                        <CardTitle class="text-2xl">{{
                            resumen.total
                        }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription>Pendientes</CardDescription>
                        <CardTitle class="text-2xl">{{
                            resumen.pendientes
                        }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader>
                        <CardDescription
                            >Horas de atención programadas</CardDescription
                        >
                        <CardTitle class="text-2xl"
                            >{{ resumen.horas }} h</CardTitle
                        >
                    </CardHeader>
                </Card>
            </div>

            <Card>
                <CardContent class="flex flex-col gap-4">
                    <!-- Filtros y leyenda -->
                    <div class="flex flex-wrap items-center gap-3">
                        <select v-model="filtros.estado" :class="selectClass">
                            <option value="">Todos (sin cancelados)</option>
                            <option v-for="e in estados" :key="e" :value="e">
                                {{ e }}
                            </option>
                        </select>

                        <select v-model="filtros.tipo" :class="selectClass">
                            <option value="">Todos los tipos</option>
                            <option v-for="t in tipos" :key="t" :value="t">
                                {{ t }}
                            </option>
                        </select>

                        <div
                            class="flex flex-wrap items-center gap-4 text-xs text-muted-foreground md:ml-auto"
                        >
                            <span
                                v-for="(clase, estado) in PUNTOS"
                                :key="estado"
                                class="flex items-center gap-1.5"
                            >
                                <span
                                    class="size-2.5 rounded-full"
                                    :class="clase"
                                />
                                {{ estado }}
                            </span>
                        </div>
                    </div>

                    <TooltipProvider :delay-duration="150">
                        <FullCalendar ref="calendario" :options="opciones">
                            <template #eventContent="arg">
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <div
                                            class="h-full w-full overflow-hidden px-1 py-0.5 text-xs leading-tight"
                                        >
                                            <span
                                                v-if="arg.timeText"
                                                class="font-semibold"
                                            >
                                                {{ arg.timeText }}
                                            </span>
                                            <div class="truncate">
                                                {{ arg.event.title }}
                                            </div>
                                        </div>
                                    </TooltipTrigger>
                                    <TooltipContent
                                        side="top"
                                        class="flex flex-col gap-1"
                                    >
                                        <span class="font-medium">{{
                                            arg.event.title
                                        }}</span>
                                        <span
                                            class="font-mono text-xs opacity-80"
                                        >
                                            {{ arg.event.extendedProps.rut }}
                                        </span>
                                        <span class="text-xs">{{
                                            arg.event.extendedProps.tipo
                                        }}</span>
                                        <span class="text-xs">
                                            {{
                                                textoHora(
                                                    arg.event.extendedProps,
                                                )
                                            }}
                                            ·
                                            {{ arg.event.extendedProps.estado }}
                                        </span>
                                    </TooltipContent>
                                </Tooltip>
                            </template>
                        </FullCalendar>
                    </TooltipProvider>
                </CardContent>
            </Card>
        </div>

        <!-- Detalle de un control -->
        <Dialog
            :open="!!seleccionado"
            @update:open="(abierto) => !abierto && (seleccionado = null)"
        >
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ seleccionado?.titulo }}</DialogTitle>
                    <DialogDescription>
                        <span class="font-mono">{{ seleccionado?.rut }}</span>
                    </DialogDescription>
                </DialogHeader>

                <dl
                    v-if="seleccionado"
                    class="grid gap-3 text-sm sm:grid-cols-2"
                >
                    <div class="flex flex-col gap-1">
                        <dt
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Fecha
                        </dt>
                        <dd>{{ formatDate(seleccionado.fecha) }}</dd>
                    </div>
                    <div class="flex flex-col gap-1">
                        <dt
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Hora
                        </dt>
                        <dd>{{ textoHora(seleccionado) }}</dd>
                    </div>
                    <div class="flex flex-col gap-1">
                        <dt
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Tipo
                        </dt>
                        <dd>{{ seleccionado.tipo }}</dd>
                    </div>
                    <div class="flex flex-col gap-1">
                        <dt
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Estado
                        </dt>
                        <dd>
                            <Badge variant="secondary">{{
                                seleccionado.estado
                            }}</Badge>
                        </dd>
                    </div>
                </dl>

                <DialogFooter>
                    <Button variant="outline" @click="seleccionado = null"
                        >Cerrar</Button
                    >
                    <Button v-if="seleccionado && puede('ficha.ver')" as-child>
                        <Link :href="`/adultos/${seleccionado.adulto_id}/ficha`"
                            >Abrir ficha del adulto</Link
                        >
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style>
/* Los colores del calendario siguen el tema de la app (claro y oscuro).
   Si tus variables de color son del tipo hsl(var(--border)), envuélvelas igual. */
.agenda .fc {
    --fc-border-color: var(--border);
    --fc-page-bg-color: var(--background);
    --fc-neutral-bg-color: var(--muted);
    --fc-neutral-text-color: var(--muted-foreground);
    --fc-today-bg-color: color-mix(in oklab, var(--primary) 8%, transparent);
    --fc-now-indicator-color: var(--destructive);
    --fc-button-bg-color: var(--primary);
    --fc-button-border-color: var(--primary);
    --fc-button-text-color: var(--primary-foreground);
    --fc-button-hover-bg-color: color-mix(in oklab, var(--primary) 85%, black);
    --fc-button-hover-border-color: color-mix(
        in oklab,
        var(--primary) 85%,
        black
    );
    --fc-button-active-bg-color: color-mix(in oklab, var(--primary) 70%, black);
    --fc-button-active-border-color: color-mix(
        in oklab,
        var(--primary) 70%,
        black
    );
    color: var(--foreground);
    font-size: 0.875rem;
}

.agenda .fc .fc-toolbar-title {
    font-size: 1.125rem;
    font-weight: 600;
}

.agenda .fc-event {
    cursor: pointer;
}

/* Un color por estado */
.agenda .evento-pendiente {
    --fc-event-bg-color: var(--primary);
    --fc-event-border-color: var(--primary);
    --fc-event-text-color: var(--primary-foreground);
}

.agenda .evento-realizado {
    --fc-event-bg-color: oklch(0.55 0.13 155);
    --fc-event-border-color: oklch(0.55 0.13 155);
    --fc-event-text-color: white;
}

.agenda .evento-no-asistio {
    --fc-event-bg-color: oklch(0.8 0.15 80);
    --fc-event-border-color: oklch(0.8 0.15 80);
    --fc-event-text-color: oklch(0.2 0 0);
}

.agenda .evento-cancelado {
    --fc-event-bg-color: var(--destructive);
    --fc-event-border-color: var(--destructive);
    --fc-event-text-color: white;
    opacity: 0.6;
    text-decoration: line-through;
}
</style>
