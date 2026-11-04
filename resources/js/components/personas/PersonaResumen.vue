<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { edit } from '@/routes/personas';

const props = defineProps({
    persona: { type: Object, required: true },
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
    {
        label: 'Edad',
        value:
            props.persona.edad != null ? `${props.persona.edad} años` : null,
    },
    { label: 'Sexo', value: props.persona.sexo, capitalize: true },
    { label: 'Email', value: props.persona.email },
]);
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-start justify-between gap-4">
            <div class="flex flex-col gap-1.5">
                <CardTitle>{{ fullName }}</CardTitle>
                <CardDescription>
                    Informacion personal. Se edita desde ficha de la persona.
                </CardDescription>
            </div>
            <Button as-child variant="ghost" size="sm">
                <Link :href="edit.url(persona.id)">
                    <Pencil class="size-4" />
                    Editar persona
                </Link>
            </Button>
        </CardHeader>
        <CardContent>
            <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div
                    v-for="dato in datos"
                    :key="dato.label"
                    class="flex flex-col gap-1"
                >
                    <dt
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
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
</template>