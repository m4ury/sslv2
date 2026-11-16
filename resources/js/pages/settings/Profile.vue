<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Configuración de perfil',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Configuración de perfil" />

    <h1 class="sr-only">Configuración de perfil</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile"
            description="Actualiza tu informacion"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="rut">Rut</Label>
                <Input
                    id="rut"
                    class="mt-1 block w-full"
                    name="rut"
                    :default-value="user.rut"
                    required
                    placeholder="Rut ej.: 16000000-k"
                />
                <InputError class="mt-2" :message="errors.rut" />
            </div>
            <div class="grid gap-2">
                <Label for="name">Nombres</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Nombre completo"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="apellido_paterno">Apellido paterno</Label>
                <Input
                    id="apellido_paterno"
                    class="mt-1 block w-full"
                    name="apellido_paterno"
                    :default-value="user.apellido_paterno"
                    required
                    placeholder="Apellido paterno"
                />
                <InputError class="mt-2" :message="errors.apellido_paterno" />
            </div>

            <div class="grid gap-2">
                <Label for="apellido_materno">apellido_materno</Label>
                <Input
                    id="apellido_materno"
                    class="mt-1 block w-full"
                    name="apellido_materno"
                    :default-value="user.apellido_materno"
                    required
                    placeholder="Apellido materno"
                />
                <InputError class="mt-2" :message="errors.apellido_materno" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Email"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Guardar</Button
                >
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>
