<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PersonaResumen from '@/components/personas/PersonaResumen.vue';
import RolEstadoCard from '@/components/roles/RolEstadoCard.vue';
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
import { Textarea } from '@/components/ui/textarea';
import { show as showPersona } from '@/routes/personas';

const props = defineProps({
    // Persona a la que pertenece el rol (siempre requerida)
    persona: { type: Object, required: true },
    // Registro existente del rol; si viene, es modo edición
    registro: { type: Object, default: null },
    // Nombre del rol: "Adulto", "Socio", "Colaborador"
    rolLabel: { type: String, required: true },
    // Secciones y campos propios del rol (ver config/roles/*.js)
    sections: { type: Array, required: true },
    storeUrl: { type: String, required: true },
    updateUrl: { type: String, default: null },
    //dar de baja y reactivar
    bajaUrl: { type: String, default: null },
    reactivarUrl: { type: String, default: null },
});

const isEdit = computed(() => !!props.registro?.id);

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

const title = computed(() =>
    isEdit.value ? `Editar ${props.rolLabel}` : `Registrar como ${props.rolLabel}`,
);

const description = computed(() =>
    isEdit.value
        ? `Modificando los datos propios de ${props.rolLabel.toLowerCase()}.`
        : `Completa los datos de ${props.rolLabel.toLowerCase()}.`,
);

// Al crear se ocultan los campos marcados editOnly (y las secciones que
// queden sin campos). Al editar se muestra todo.
const visibleSections = computed(() =>
    props.sections
        .map((section) => ({
            ...section,
            fields: section.fields.filter(
                (field) => isEdit.value || !field.editOnly,
            ),
        }))
        .filter((section) => section.fields.length > 0),
);

const allFields = computed(() =>
    visibleSections.value.flatMap((section) => section.fields),
);

function initialValue(field) {
    const value = props.registro?.[field.name];
    if (value == null) return '';
    // El input type="date" requiere formato YYYY-MM-DD
    return field.type === 'date' ? String(value).substring(0, 10) : value;
}

const form = useForm({
    persona_id: props.persona.id,
    ...Object.fromEntries(
        allFields.value.map((field) => [field.name, initialValue(field)]),
    ),
});

function submitForm() {
    if (isEdit.value) {
        form.put(props.updateUrl);
    } else {
        form.post(props.storeUrl);
    }
}

// Acepta ['A', 'B'] o [{ value, label }]. Si el valor guardado ya no
// está en la lista, igual se muestra al editar.
function optionsFor(field) {
    const base = (field.options ?? []).map((o) =>
        typeof o === 'string' ? { value: o, label: o } : o,
    );
    const current = form[field.name];
    if (current && !base.some((o) => o.value === current)) {
        base.push({ value: current, label: current });
    }
    return base;
}

function minFor(field) {
    return field.minFrom ? form[field.minFrom] || undefined : undefined;
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
                <Link :href="showPersona.url(persona.id)">
                    <ArrowLeft class="size-4" />
                    Volver a la persona
                </Link>
            </Button>
        </div>

        <PersonaResumen :persona="persona" />

        <RolEstadoCard
            v-if="isEdit && bajaUrl && reactivarUrl"
            :registro="registro"
            :rol-label="rolLabel"
            :nombre="fullName"
            :baja-url="bajaUrl"
            :reactivar-url="reactivarUrl"
        />

        <form @submit.prevent="submitForm">
            <Card>
                <CardHeader>
                    <CardTitle>Datos de {{ rolLabel.toLowerCase() }}</CardTitle>
                    <CardDescription>
                        Los campos marcados con
                        <span class="text-destructive">*</span> son
                        obligatorios.
                    </CardDescription>
                </CardHeader>

                <CardContent class="flex flex-col gap-8">
                    <InputError :message="form.errors.persona_id" />

                    <section
                        v-for="section in visibleSections"
                        :key="section.title"
                        class="flex flex-col gap-4"
                    >
                        <h2
                            class="border-b pb-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            {{ section.title }}
                        </h2>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div
                                v-for="field in section.fields"
                                :key="field.name"
                                class="flex flex-col gap-2"
                                :class="{ 'md:col-span-2': field.span === 2 }"
                            >
                                <Label :for="field.name">
                                    {{ field.label }}
                                    <span
                                        v-if="field.required"
                                        class="text-destructive"
                                        >*</span
                                    >
                                </Label>

                                <Textarea
                                    v-if="field.type === 'textarea'"
                                    :id="field.name"
                                    v-model="form[field.name]"
                                    :rows="field.rows ?? 4"
                                    :placeholder="field.placeholder"
                                    :aria-invalid="!!form.errors[field.name]"
                                />

                                <select
                                    v-else-if="field.type === 'select'"
                                    :id="field.name"
                                    v-model="form[field.name]"
                                    class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive"
                                    :aria-invalid="!!form.errors[field.name]"
                                    :required="field.required"
                                >
                                    <option value="">Seleccione...</option>
                                    <option
                                        v-for="option in optionsFor(field)"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>

                                <Input
                                    v-else
                                    :id="field.name"
                                    v-model="form[field.name]"
                                    :type="field.type ?? 'text'"
                                    :placeholder="field.placeholder"
                                    :min="minFor(field)"
                                    :aria-invalid="!!form.errors[field.name]"
                                    :required="field.required"
                                />

                                <InputError :message="form.errors[field.name]" />
                            </div>
                        </div>
                    </section>

                    <!-- Para casos especiales que el esquema no cubra -->
                    <slot :form="form" />
                </CardContent>

                <CardFooter class="flex justify-end gap-2 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link :href="showPersona.url(persona.id)">
                            Cancelar
                        </Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="size-4 animate-spin"
                        />
                        <Save v-else class="size-4" />
                        {{
                            isEdit
                                ? 'Guardar cambios'
                                : `Registrar ${rolLabel.toLowerCase()}`
                        }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
