<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    HandHeart,
    Handshake,
    Pencil,
    Trash2,
    UserRound,
} from '@lucide/vue';
import { computed } from 'vue';
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
    create as createColaborador,
    edit as editColaborador,
    show as showColaborador,
} from '@/routes/colaboradores';
import { destroy, edit, index } from '@/routes/personas';

const props = defineProps({
    persona: { type: Object, required: true },
    roles: {
        type: Object,
        default: () => ({ socio: null, adulto: null, colaborador: null }),
    },
});

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

const datos = computed(() => [
    { label: 'RUT', value: props.persona.rut, mono: true },
    { label: 'Nombres', value: props.persona.nombres },
    { label: 'Apellido paterno', value: props.persona.apellido_paterno },
    { label: 'Apellido materno', value: props.persona.apellido_materno },
    {
        label: 'Fecha de nacimiento',
        value: formatDate(props.persona.fecha_nacimiento),
    },
    {
        label: 'Edad',
        value:
            props.persona.edad != null ? `${props.persona.edad} años` : '—',
    },
    { label: 'Sexo', value: props.persona.sexo, capitalize: true },
    { label: 'Email', value: props.persona.email },
]);

// Cada rol: al transicionar se abre su formulario con ?persona_id=
// Si ya existe, se enlaza a su ficha. Ajusta las rutas a las tuyas.
const transiciones = computed(() => [
    {
        key: 'socio',
        label: 'Socio',
        description: 'Registrar a esta persona como socio de la agrupación.',
        icon: Handshake,
        createUrl: `/socios/create?persona_id=${props.persona.id}`,
        editUrl: props.roles.socio ? `/socios/${props.roles.socio.id}/edit` : null,
        showUrl: props.roles.socio ? `/socios/${props.roles.socio.id}` : null,

    },
    {
        key: 'adulto',
        label: 'Adulto mayor',
        description: 'Incorporar como adulto mayor perteneciente a la agrupación.',
        icon: UserRound,
        createUrl: `/adultos/create?persona_id=${props.persona.id}`,
        editUrl: props.roles.adulto
            ? `/adultos/${props.roles.adulto.id}/edit`
            : null,
        showUrl: props.roles.adulto ? `/adultos/${props.roles.adulto.id}` : null,
    },
    {
        key: 'colaboradores',
        label: 'Colaborador',
        description: 'Registrar como colaborador de la agrupación.',
        icon: HandHeart,
        createUrl: createColaborador.url({
            query: { persona_id: props.persona.id },
        }),
        editUrl: props.roles.colaborador
            ? editColaborador.url(props.roles.colaborador.id)
            : null,
        showUrl: props.roles.colaborador
            ? showColaborador.url(props.roles.colaborador.id)
            : null,
    },
]);

const rolesActivos = computed(() =>
    transiciones.value.filter((t) => t.editUrl),
);

function deletePersona() {
    if (
        window.confirm(
            `¿Estás seguro de que deseas eliminar a ${fullName.value}?`,
        )
    ) {
        router.delete(destroy.url(props.persona.id));
    }
}

// Evita el desfase de zona horaria al parsear "YYYY-MM-DD"
function formatDate(value) {
    if (!value) return '—';
    const [y, m, d] = value.substring(0, 10).split('-');
    return `${d}-${m}-${y}`;
}
</script>

<template>
    <Head :title="fullName" />

    <div class="mx-auto flex max-w-5xl flex-col gap-6 p-4 md:p-6">
        <!-- Encabezado -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold">{{ fullName }}</h1>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-sm text-muted-foreground">
                        {{ persona.rut }}
                    </span>
                    <Badge v-for="rol in rolesActivos" :key="rol.key">
                        {{ rol.label }}
                    </Badge>
                    <Badge v-if="rolesActivos.length === 0" variant="outline">
                        Solo persona
                    </Badge>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button as-child variant="outline">
                    <Link :href="index.url()">
                        <ArrowLeft class="size-4" />
                        Volver al listado
                    </Link>
                </Button>
                <Button as-child>
                    <Link :href="edit.url(persona.id)">
                        <Pencil class="size-4" />
                        Editar
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <!-- Datos -->
            <Card class="lg:col-span-3">
                <CardHeader>
                    <CardTitle>Datos de la persona</CardTitle>
                    <CardDescription>
                        Información compartida con todos sus roles en la
                        agrupación.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div
                            v-for="dato in datos"
                            :key="dato.label"
                            class="flex flex-col gap-1"
                        >
                            <dt class="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                {{ dato.label }}
                            </dt>
                            <dd
                                class="text-sm wrap-break-word"
                                :class="{
                                    'font-mono': dato.mono,
                                    capitalize: dato.capitalize,
                                }"
                            >
                                {{ dato.value || '—' }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <!-- Transiciones -->
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>Pertenencia a la agrupación</CardTitle>
                    <CardDescription>
                        Los datos de esta persona se reutilizan, solo se piden
                        los campos propios de cada rol.
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-3">
                    <div
                        v-for="rol in transiciones"
                        :key="rol.key"
                        class="flex flex-col gap-3 rounded-lg border p-4"
                    >
                        <div class="flex items-start gap-3">
                            <component
                                :is="rol.icon"
                                class="mt-0.5 size-5 shrink-0 text-muted-foreground"
                            />
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium">
                                    {{ rol.label }}
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ rol.description }}
                                </span>
                            </div>
                        </div>

                        <Button
                            v-if="rol.editUrl"
                            as-child
                            variant="secondary"
                            size="sm"
                        >
                            <Link :href="rol.editUrl">
                                <CheckCircle2 class="size-4" />
                                Ya es {{ rol.label.toLowerCase() }} · Ver ficha
                            </Link>
                        </Button>
                        <Button v-else as-child size="sm">
                            <Link :href="rol.createUrl">
                                Registrar como {{ rol.label.toLowerCase() }}
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Zona de peligro -->
        <Card class="border-destructive/40">
            <CardContent
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-medium">Eliminar persona</p>
                    <p class="text-sm text-muted-foreground">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <Button variant="destructive" @click="deletePersona">
                    <Trash2 class="size-4" />
                    Eliminar persona
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
