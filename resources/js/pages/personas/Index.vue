<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Eye,
    HandHeart,
    Handshake,
    HeartHandshake,
    Pencil,
    Plus,
    Search,
    Trash2,
    UserRound,
    Users,
} from '@lucide/vue';
import { onBeforeUnmount, ref, watch } from 'vue';
import { create, destroy, edit, index, show } from '@/routes/personas';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import { useConfirm } from '@/composables/useConfirm';

const { confirmar } = useConfirm();

const props = defineProps({
    personas: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

const search = ref(props.filters.search ?? '');
let timeout;

watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            index.url(),
            { search: value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});

onBeforeUnmount(() => clearTimeout(timeout));

const roleDefinitions = [
    { key: 'socio', label: 'Socio', icon: Handshake },
    { key: 'colaborador', label: 'Colaborador', icon: HandHeart },
    { key: 'cuidador', label: 'Cuidador', icon: HeartHandshake },
];

async function deletePersona(persona) {
    const ok = await confirmar({
        titulo: `¿Eliminar a ${persona.full_name}?`,
        descripcion: 'Esta acción no se puede deshacer.',
        confirmarTexto: 'Eliminar',
        destructivo: true,
    });

    if (ok) {
        router.delete(destroy.url(persona.id), { preserveScroll: true });
    }
}

function rolesFor(persona) {
    return roleDefinitions.filter(({ key }) => persona[key]);
}

function roleCaption(persona, role) {
    const estado = persona[role.key].estado;

    return estado ? `${role.label} · ${estado}` : role.label;
}

// Evita el desfase de zona horaria al parsear "YYYY-MM-DD"
function formatDate(value) {
    if (!value) return '—';
    const [y, m, d] = value.substring(0, 10).split('-');
    return `${d}-${m}-${y}`;
}
</script>

<template>
    <Head title="Personas" />

    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
        <!-- Encabezado -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold">Personas</h1>
                <p class="text-sm text-muted-foreground">
                    {{ personas.total }}
                    {{ personas.total === 1 ? 'registro' : 'registros' }}
                </p>
            </div>
            <Button as-child>
                <Link :href="create.url()">
                    <Plus class="size-4" />
                    Crear Persona
                </Link>
            </Button>
        </div>

        <Card>
            <CardContent class="flex flex-col gap-4">
                <!-- Buscador -->
                <div class="relative max-w-sm">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        type="search"
                        placeholder="Buscar por RUT, nombres y apellidos..."
                        class="pl-9"
                    />
                </div>

                <!-- Tabla -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>RUT</TableHead>
                                <TableHead>Nombre completo</TableHead>
                                <TableHead>Fecha de nacimiento</TableHead>
                                <TableHead>Edad</TableHead>
                                <TableHead>Sexo</TableHead>
                                <TableHead>Roles</TableHead>
                                <TableHead class="text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="persona in personas.data"
                                :key="persona.id"
                            >
                                <TableCell class="font-mono">
                                    {{ persona.rut }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ persona.full_name }}
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(persona.fecha_nacimiento) }}
                                </TableCell>
                                <TableCell>{{ persona.edad }}</TableCell>
                                <TableCell>
                                    <Badge
                                        variant="secondary"
                                        class="capitalize"
                                    >
                                        {{ persona.sexo }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <TooltipProvider :delay-duration="200">
                                        <div
                                            v-if="rolesFor(persona).length > 0"
                                            class="flex items-center gap-1"
                                        >
                                            <Tooltip
                                                v-for="role in rolesFor(persona)"
                                                :key="role.key"
                                            >
                                                <TooltipTrigger as-child>
                                                    <button
                                                        type="button"
                                                        :aria-label="
                                                            roleCaption(
                                                                persona,
                                                                role,
                                                            )
                                                        "
                                                        class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                                    >
                                                        <component
                                                            :is="role.icon"
                                                            class="size-4"
                                                        />
                                                    </button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{
                                                        roleCaption(
                                                            persona,
                                                            role,
                                                        )
                                                    }}
                                                </TooltipContent>
                                            </Tooltip>
                                        </div>
                                        <span
                                            v-else
                                            class="text-sm text-muted-foreground"
                                        >
                                            Sin roles
                                        </span>
                                    </TooltipProvider>
                                </TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-1">
                                        <Button
                                            as-child
                                            variant="ghost"
                                            size="icon"
                                            title="Ver"
                                        >
                                            <Link :href="show.url(persona.id)">
                                                <Eye class="size-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            as-child
                                            variant="ghost"
                                            size="icon"
                                            title="Editar"
                                        >
                                            <Link :href="edit.url(persona.id)">
                                                <Pencil class="size-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            title="Eliminar"
                                            class="text-destructive hover:text-destructive"
                                            @click="deletePersona(persona)"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <!-- Estado vacío -->
                            <TableRow v-if="personas.data.length === 0">
                                <TableCell colspan="7" class="h-32 text-center">
                                    <div
                                        class="flex flex-col items-center gap-2 text-muted-foreground"
                                    >
                                        <Users class="size-8" />
                                        <span>
                                            {{
                                                search
                                                    ? 'No se encontraron resultados para tu búsqueda.'
                                                    : 'Aún no hay personas registradas.'
                                            }}
                                        </span>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pie: resumen + paginación -->
                <div
                    v-if="personas.total > 0"
                    class="flex flex-col items-center justify-between gap-3 sm:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        Mostrando {{ personas.from }} a {{ personas.to }} de
                        {{ personas.total }}
                    </p>
                    <Pagination :links="personas.links" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>
