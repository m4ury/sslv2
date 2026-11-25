<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowUpRight,
    Calendar,
    ChevronRight,
    ClipboardCheck,
    CreditCard,
    DollarSign,
    Heart,
    LayoutDashboard,
    Package,
    ShieldAlert,
    TrendingDown,
    TrendingUp,
    Users,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import BarrasMensuales from '@/components/dashboard/BarrasMensuales.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermisos } from '@/composables/usePermisos';
import { formatCLP, formatDate } from '@/config/ficha';

const props = defineProps({
    alertas: { type: Array, default: () => [] },
    agenda: { type: Object, default: null },
    clinico: { type: Object, default: null },
    personas: { type: Object, default: null },
    cuotas: { type: Object, default: null },
    caja: { type: Object, default: null },
    reservados: { type: Object, default: null },
    inventario: { type: Object, default: null },
});

const page = usePage();
const { puede } = usePermisos();

const nombre = computed(() => (page.props.auth?.user?.name ?? '').split(' ')[0]);

const saludo = computed(() => {
    const hora = new Date().getHours();
    return hora < 12 ? 'Buenos días' : hora < 20 ? 'Buenas tardes' : 'Buenas noches';
});

const fechaHoy = computed(() => {
    const texto = new Date().toLocaleDateString('es-CL', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
    return texto.charAt(0).toUpperCase() + texto.slice(1);
});

const sinContenido = computed(() =>
    [props.agenda, props.clinico, props.personas, props.cuotas, props.caja, props.reservados, props.inventario].every(
        (w) => w === null,
    ),
);

const NIVELES = {
    danger: 'border-l-destructive',
    warning: 'border-l-amber-500',
    info: 'border-l-sky-500',
};

const ESTADOS = { Pendiente: 'default', Realizado: 'secondary', 'No asistió': 'destructive' };

const accesos = computed(() =>
    [
        { titulo: 'Agenda', href: '/agenda', permiso: 'agenda.ver' },
        { titulo: 'Personas', href: '/personas?type=socio', permiso: 'personas.ver' },
        { titulo: 'Cuotas', href: '/cuotas', permiso: 'cuotas.ver' },
        { titulo: 'Flujo de caja', href: '/caja', permiso: 'caja.ver' },
        { titulo: 'Inventario', href: '/inventario', permiso: 'inventario.ver' },
        { titulo: 'Informe de aportes', href: '/aportes/informe', permiso: 'aportes.informe' },
        { titulo: 'Ingresos bancarios', href: '/banco', permiso: 'banco.ver' },
    ].filter((a) => puede(a.permiso)),
);
</script>

<template>
    <Head title="Inicio" />

    <div class="mx-3 flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <!-- Saludo -->
        <div class="animate-in-up relative overflow-hidden rounded-2xl bg-linear-to-br from-primary/10 via-primary/5 to-transparent p-6 sm:p-8 shadow-sm">
            <div class="relative z-10 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight">
                        {{ saludo }}<span v-if="nombre" class="text-primary">, {{ nombre }}</span>
                    </h1>
                    <p class="text-muted-foreground flex items-center gap-2 mt-1">
                        <Calendar class="size-4" />
                        {{ fechaHoy }}
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <Button as-child variant="default" size="sm" class="rounded-full px-6 shadow-lg transition-transform hover:scale-105 active:scale-95">
                        <Link href="/agenda">
                            <Calendar class="size-4 mr-2" />
                            Ver mi agenda
                        </Link>
                    </Button>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -right-12 -top-12 size-48 rounded-full bg-primary/5 blur-3xl" />
        </div>

        <!-- Sin permisos -->
        <Card v-if="sinContenido">
            <CardHeader>
                <CardTitle>Tu cuenta aún no tiene accesos asignados</CardTitle>
                <CardDescription>
                    Pídele a un administrador que le asigne una profesión con permisos a tu usuario.
                </CardDescription>
            </CardHeader>
        </Card>

        <!-- Requiere atención -->
        <Card v-if="alertas.length" class="animate-in-up stagger-1">
            <CardHeader>
                <CardTitle>Requiere atención</CardTitle>
                <CardDescription>Lo urgente de los módulos a los que tienes acceso.</CardDescription>
            </CardHeader>
            <CardContent class="flex flex-col gap-2">
                <component
                    :is="a.href ? Link : 'div'"
                    v-for="(a, i) in alertas"
                    :key="i"
                    :href="a.href ?? undefined"
                    class="rounded-md border border-l-4 px-4 py-3 text-sm"
                    :class="[NIVELES[a.nivel], a.href ? 'transition-colors hover:bg-accent/40' : '']"
                >
                    {{ a.texto }}
                </component>
            </CardContent>
        </Card>

        <!-- Agenda y seguimiento clínico -->
        <div v-if="agenda || clinico" class="animate-in-up stagger-2 grid gap-6 grid-cols-[repeat(auto-fit,minmax(min(100%,24rem),1fr))]">
            <Card v-if="agenda">
                <CardHeader>
                    <CardTitle>Agenda de hoy</CardTitle>
                    <CardDescription>
                        {{ agenda.hoy }} {{ agenda.hoy === 1 ? 'control' : 'controles' }} hoy ·
                        {{ agenda.proximos }} en los próximos 7 días
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-3">
                    <div
                        v-for="c in agenda.lista"
                        :key="c.id"
                        class="flex items-center justify-between gap-3 rounded-md border px-3 py-2 text-sm"
                    >
                        <div class="flex min-w-0 flex-col">
                            <component
                                :is="puede('ficha.ver') ? Link : 'span'"
                                :href="puede('ficha.ver') ? `/pacientes/${c.paciente_id}/ficha` : undefined"
                                class="truncate font-medium"
                                :class="{ 'underline-offset-4 hover:underline': puede('ficha.ver') }"
                            >
                                {{ c.paciente }}
                            </component>
                            <span class="text-xs text-muted-foreground">
                                {{ c.hora ? `${c.hora} – ${c.fin}` : 'Sin hora' }} · {{ c.tipo }}
                            </span>
                        </div>
                        <Badge :variant="ESTADOS[c.estado] ?? 'secondary'">{{ c.estado }}</Badge>
                    </div>

                    <p v-if="agenda.lista.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                        No hay controles programados para hoy.
                    </p>
                    <p v-else-if="agenda.hoy > agenda.lista.length" class="text-xs text-muted-foreground">
                        Y {{ agenda.hoy - agenda.lista.length }} más en la agenda.
                    </p>

                    <Button as-child variant="outline" size="sm" class="self-start">
                        <Link href="/agenda">Abrir agenda</Link>
                    </Button>
                </CardContent>
            </Card>

            <Card v-if="clinico">
                <CardHeader>
                    <CardTitle>Seguimiento clínico</CardTitle>
                    <CardDescription>Credenciales y socios que conviene revisar.</CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-5">
                    <section class="flex flex-col gap-2">
                        <h3 class="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Credenciales por revisar ({{ clinico.credenciales_total }})
                        </h3>
                        <div
                            v-for="c in clinico.credenciales"
                            :key="c.id"
                            class="flex items-center justify-between gap-3 text-sm"
                        >
                            <div class="flex min-w-0 flex-col">
                                <Link
                                    :href="`/pacientes/${c.paciente_id}/ficha`"
                                    class="truncate font-medium underline-offset-4 hover:underline"
                                >
                                    {{ c.paciente }}
                                </Link>
                                <span class="text-xs text-muted-foreground">
                                    {{ c.tipo }} · vence {{ formatDate(c.vencimiento) }}
                                </span>
                            </div>
                            <Badge :variant="c.vencida ? 'destructive' : 'default'">
                                {{ c.vencida ? 'Vencida' : 'Por vencer' }}
                            </Badge>
                        </div>
                        <p v-if="clinico.credenciales.length === 0" class="text-sm text-muted-foreground">
                            Todas las credenciales están al día.
                        </p>
                    </section>

                    <section class="flex flex-col gap-2">
                        <h3 class="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Sin control en {{ clinico.dias_sin_control }} días ni control programado ({{
                                clinico.sin_control
                            }})
                        </h3>
                        <Link
                            v-for="a in clinico.sin_control_lista"
                            :key="a.paciente_id"
                            :href="`/pacientes/${a.paciente_id}/ficha`"
                            class="text-sm underline-offset-4 hover:underline"
                        >
                            {{ a.paciente }}
                        </Link>
                        <p v-if="clinico.sin_control === 0" class="text-sm text-muted-foreground">
                            Todos los pacientes activos tienen seguimiento reciente.
                        </p>
                        <p v-else-if="clinico.sin_control > clinico.sin_control_lista.length" class="text-xs text-muted-foreground">
                            Y {{ clinico.sin_control - clinico.sin_control_lista.length }} más.
                        </p>
                    </section>
                </CardContent>
            </Card>
        </div>

        <!-- Comunidad -->
        <section v-if="personas" class="animate-in-up stagger-3 flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <Users class="size-4 text-muted-foreground" />
                <h2 class="text-sm font-semibold tracking-wide text-muted-foreground uppercase">Comunidad</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard label="Pacientes activos" :value="personas.pacientes" href="/personas?type=paciente" :icon="Users" />
                <StatCard
                    v-if="puede('aportes.ver') && personas.colaboradores !== null"
                    label="Colaboradores activos"
                    :value="personas.colaboradores"
                    href="/personas?type=colaborador"
                    :icon="Heart"
                    tone="success"
                />
                <StatCard label="Cuidadores activos" :value="personas.cuidadores" href="/personas?type=cuidador" :icon="ShieldAlert" tone="info" />
                <StatCard
                    label="Sin cuidador principal"
                    :value="personas.pacientes_sin_cuidador"
                    :tone="personas.pacientes_sin_cuidador > 0 ? 'warning' : 'default'"
                    :icon="AlertCircle"
                />
            </div>
        </section>

        <!-- Cuotas -->
        <section v-if="cuotas" class="animate-in-up stagger-4 flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <CreditCard class="size-4 text-muted-foreground" />
                <h2 class="text-sm font-semibold tracking-wide text-muted-foreground uppercase">Cuotas</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <StatCard
                    :label="`Cobranza de ${cuotas.periodo_etiqueta}`"
                    :value="cuotas.porcentaje === null ? '—' : `${cuotas.porcentaje}%`"
                    :hint="
                        cuotas.porcentaje === null
                            ? 'Aún no hay cuotas emitidas este mes'
                            : `${formatCLP(cuotas.cobrado)} de ${formatCLP(cuotas.emitido)}`
                    "
                    href="/cuotas"
                    :icon="TrendingUp"
                    tone="info"
                />
                <StatCard
                    label="Cuotas vencidas"
                    :value="cuotas.vencidas"
                    :hint="`${cuotas.morosos} ${cuotas.morosos === 1 ? 'socio' : 'socios'} con deuda vencida`"
                    :tone="cuotas.vencidas > 0 ? 'danger' : 'default'"
                    href="/cuotas?estado=Vencida"
                    :icon="AlertCircle"
                />
                <StatCard
                    label="Monto adeudado vencido"
                    :value="formatCLP(cuotas.adeudado)"
                    :tone="cuotas.adeudado > 0 ? 'danger' : 'default'"
                    href="/cuotas?estado=Vencida"
                    :icon="DollarSign"
                />
            </div>
        </section>

        <!-- Caja -->
        <section v-if="caja" class="animate-in-up stagger-5 flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <Wallet class="size-4 text-muted-foreground" />
                <h2 class="text-sm font-semibold tracking-wide text-muted-foreground uppercase">Caja</h2>
            </div>

            <Card v-if="!caja.configurada">
                <CardHeader>
                    <CardTitle>La caja aún no está configurada</CardTitle>
                    <CardDescription>
                        Define el saldo inicial para ver el estado financiero.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Button as-child variant="outline"><Link href="/caja">Ir a caja</Link></Button>
                </CardContent>
            </Card>

            <div v-else class="grid gap-4 lg:grid-cols-3">
                <div class="grid gap-4 sm:grid-cols-3 lg:col-span-3">
                    <StatCard
                        label="Saldo disponible"
                        :value="formatCLP(caja.saldo)"
                        :tone="caja.saldo < 0 ? 'danger' : 'default'"
                        href="/caja"
                        :icon="Wallet"
                    />
                    <StatCard label="Ingresos del mes" :value="formatCLP(caja.ingresos_mes)" href="/caja/estado" :icon="TrendingUp" tone="success" />
                    <StatCard label="Egresos del mes" :value="formatCLP(caja.egresos_mes)" href="/caja/estado" :icon="TrendingDown" tone="danger" />
                </div>

                <Card class="lg:col-span-3">
                    <CardHeader>
                        <CardTitle>Ingresos y egresos</CardTitle>
                        <CardDescription>Últimos meses del flujo de caja (cuotas y otros ingresos).</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <BarrasMensuales :serie="caja.serie" />
                    </CardContent>
                </Card>
            </div>
        </section>

        <!-- Ingresos reservados -->
        <section v-if="reservados" class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <ShieldAlert class="size-4 text-muted-foreground" />
                <h2 class="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                    Ingresos reservados
                </h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <StatCard
                    v-if="reservados.aportes"
                    label="Aportes del mes"
                    :value="formatCLP(reservados.aportes.total)"
                    :hint="`${reservados.aportes.cantidad} ${reservados.aportes.cantidad === 1 ? 'aporte' : 'aportes'}`"
                    href="/aportes/informe"
                    :icon="Heart"
                    tone="success"
                />
                <StatCard
                    v-if="reservados.banco"
                    label="Ingresos bancarios del mes"
                    :value="formatCLP(reservados.banco.total)"
                    :hint="`${reservados.banco.cantidad} ${reservados.banco.cantidad === 1 ? 'abono' : 'abonos'}`"
                    href="/banco"
                    :icon="TrendingUp"
                    tone="info"
                />
            </div>
        </section>

        <!-- Inventario -->
        <section v-if="inventario" class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <Package class="size-4 text-muted-foreground" />
                <h2 class="text-sm font-semibold tracking-wide text-muted-foreground uppercase">Inventario</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <StatCard
                    label="Préstamos pendientes"
                    :value="inventario.prestamos_pendientes"
                    href="/inventario/prestamos"
                    :icon="Package"
                    tone="info"
                />
                <StatCard
                    label="Préstamos vencidos"
                    :value="inventario.vencidos"
                    :tone="inventario.vencidos > 0 ? 'danger' : 'default'"
                    href="/inventario/prestamos?estado=Vencido"
                    :icon="AlertCircle"
                />
                <StatCard
                    label="Bajo el stock mínimo"
                    :value="inventario.bajo_stock"
                    :tone="inventario.bajo_stock > 0 ? 'warning' : 'default'"
                    href="/inventario"
                    :icon="TrendingDown"
                />
            </div>

            <Card v-if="inventario.vencidos_lista.length">
                <CardHeader>
                    <CardTitle>Préstamos vencidos</CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-2">
                    <div
                        v-for="p in inventario.vencidos_lista"
                        :key="p.id"
                        class="flex items-center justify-between gap-3 text-sm"
                    >
                        <div class="flex min-w-0 flex-col">
                            <Link
                                :href="`/inventario/${p.articulo_id}`"
                                class="truncate font-medium underline-offset-4 hover:underline"
                            >
                                {{ p.articulo }}
                            </Link>
                            <span class="text-xs text-muted-foreground">
                                {{ p.pendiente }} {{ p.pendiente === 1 ? 'unidad' : 'unidades' }} con
                                {{ p.prestatario }}
                            </span>
                        </div>
                        <Badge variant="destructive">{{ p.dias }} {{ p.dias === 1 ? 'día' : 'días' }} de atraso</Badge>
                    </div>
                </CardContent>
            </Card>
        </section>

        <!-- Accesos rápidos -->
        <Card v-if="accesos.length">
            <CardHeader>
                <CardTitle>Accesos rápidos</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-wrap gap-2">
                <Button v-for="a in accesos" :key="a.href" as-child variant="outline" size="sm">
                    <Link :href="a.href">{{ a.titulo }}</Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
