<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, store, update } from '@/routes/personas';

const props = defineProps({
    persona: {
        type: Object,
        default: null,
    },
});

const comunaOptions = [
    'Cauquenes',
    'Chanco',
    'Pelluhue',
    'Curico',
    'Hualane',
    'Licanten',
    'Molina',
    'Rauco',
    'Romeral',
    'Sgda Familia',
    'Teno',
    'Vichuquen',
    'Linares',
    'Colbun',
    'Longabi',
    'Parral',
    'Retiro',
    'San Javier',
    'Villa Alegre',
    'Yerbas Buenas',
    'Talca',
    'Constitucion',
    'Empedrado',
    'Maule',
    'Pelarco',
    'Pencahue',
    'Rio Claro',
    'San Clemente',
    'San Rafael',
    'Curepto',
];

const isEdit = computed(() => !!props.persona?.id);

const title = computed(() => (isEdit.value ? 'Editar Persona' : 'Crear Persona'));

const description = computed(() =>
    isEdit.value
        ? `Modificando los datos de ${props.persona.full_name ?? 'la persona'}.`
        : 'Completa los datos para registrar una nueva persona.',
);

const form = useForm({
    rut: props.persona?.rut ?? '',
    nombres: props.persona?.nombres ?? '',
    apellido_paterno: props.persona?.apellido_paterno ?? '',
    apellido_materno: props.persona?.apellido_materno ?? '',
    email: props.persona?.email ?? '',
    // El input type="date" requiere formato YYYY-MM-DD
    fecha_nacimiento: props.persona?.fecha_nacimiento?.substring(0, 10) ?? '',
    sexo: props.persona?.sexo ?? '',
    direccion: props.persona?.direccion ?? '',
    comuna: props.persona?.comuna ?? '',
});

function submitForm() {
    if (isEdit.value) {
        form.put(update.url(props.persona.id));
    } else {
        form.post(store.url());
    }
}
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex max-w-3xl flex-col gap-6 p-4 md:p-6">
        <!-- Encabezado -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold">{{ title }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <Button as-child variant="outline">
                <Link :href="index.url()">
                    <ArrowLeft class="size-4" />
                    Volver al listado
                </Link>
            </Button>
        </div>

        <form @submit.prevent="submitForm">
            <Card>
                <CardHeader>
                    <CardTitle>Datos de la persona</CardTitle>
                    <CardDescription>
                        Los campos marcados con
                        <span class="text-destructive">*</span> son
                        obligatorios.
                    </CardDescription>
                </CardHeader>

                <CardContent class="flex flex-col gap-8">
                    <!-- Identificación -->
                    <section class="flex flex-col gap-4">
                        <h2
                            class="border-b pb-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Identificación
                        </h2>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="flex flex-col gap-2 md:col-span-2">
                                <Label for="rut">
                                    RUT <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="rut"
                                    v-model="form.rut"
                                    type="text"
                                    placeholder="12345678-9"
                                    class="font-mono md:max-w-xs"
                                    :aria-invalid="!!form.errors.rut"
                                    required
                                />
                                <InputError :message="form.errors.rut" />
                            </div>

                            <div class="flex flex-col gap-2 md:col-span-2">
                                <Label for="nombres">
                                    Nombres
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombres"
                                    v-model="form.nombres"
                                    type="text"
                                    :aria-invalid="!!form.errors.nombres"
                                    required
                                />
                                <InputError :message="form.errors.nombres" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <Label for="apellido_paterno">
                                    Apellido paterno
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="apellido_paterno"
                                    v-model="form.apellido_paterno"
                                    type="text"
                                    :aria-invalid="
                                        !!form.errors.apellido_paterno
                                    "
                                    required
                                />
                                <InputError
                                    :message="form.errors.apellido_paterno"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <Label for="apellido_materno">
                                    Apellido materno
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="apellido_materno"
                                    v-model="form.apellido_materno"
                                    type="text"
                                    :aria-invalid="
                                        !!form.errors.apellido_materno
                                    "
                                    required
                                />
                                <InputError
                                    :message="form.errors.apellido_materno"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Datos personales y contacto -->
                    <section class="flex flex-col gap-4">
                        <h2
                            class="border-b pb-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Datos personales y contacto
                        </h2>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <Label for="fecha_nacimiento">
                                    Fecha de nacimiento
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="fecha_nacimiento"
                                    v-model="form.fecha_nacimiento"
                                    type="date"
                                    :aria-invalid="
                                        !!form.errors.fecha_nacimiento
                                    "
                                    required
                                />
                                <InputError
                                    :message="form.errors.fecha_nacimiento"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <Label for="sexo">
                                    Sexo
                                    <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="sexo"
                                    v-model="form.sexo"
                                    class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive"
                                    :aria-invalid="!!form.errors.sexo"
                                    required
                                >
                                    <option value="">Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                                <InputError :message="form.errors.sexo" />
                            </div>

                            <div class="flex flex-col gap-2 md:col-span-2">
                                <Label for="email">
                                    Email
                                </Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="correo@ejemplo.cl"
                                    :aria-invalid="!!form.errors.email"
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>
                    </section>

                    <!--Datos de ubicacion -->
                    <section class="flex flex-col gap-4">
                        <h2
                            class="border-b pb-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Datos ubicacion
                        </h2>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <Label for="direccion">
                                    Direccion
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="direccion"
                                    v-model="form.direccion"
                                    type="text"
                                    :aria-invalid="
                                        !!form.errors.direccion
                                    "
                                    required
                                />
                                <InputError
                                    :message="form.errors.direccion"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <Label for="comuna">
                                    Comuna
                                    <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="comuna"
                                    v-model="form.comuna"
                                    class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive"
                                    :aria-invalid="!!form.errors.comuna"
                                    required
                                >
                                    <option value="">Seleccione...</option>
                                    <option
                                        v-for="comuna in comunaOptions"
                                        :key="comuna"
                                        :value="comuna"
                                    >
                                        {{ comuna }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.comuna" />
                            </div>
                        </div>
                    </section>
                </CardContent>

                <CardFooter class="flex justify-end gap-2 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link :href="index.url()">Cancelar</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="size-4 animate-spin"
                        />
                        <Save v-else class="size-4" />
                        {{ isEdit ? 'Guardar cambios' : 'Crear persona' }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
