<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Crea una cuenta',
        description: 'Ingresa tus datos abajo para crear tu cuenta',
    },
});
</script>

<template>
    <Head title="Registrarse" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="rut">Rut</Label>
                <Input
                    id="rut"
                    type="text"
                    required
                    v-focus
                    :tabindex="1"
                    autocomplete="rut"
                    name="rut"
                    placeholder="Rut. ej.: 16000000-5"
                />
                <InputError :message="errors.rut" />
            </div>

            <div class="grid gap-2">
                <Label for="name">Nombres</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    :tabindex="2"
                    autocomplete="name"
                    name="name"
                    placeholder="Nombre completo"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-4">
                <Label for="apellido_paterno">Apellido paterno</Label>
                <Input
                    id="apellido_paterno"
                    type="text"
                    required
                    :tabindex="3"
                    autocomplete="apellido_paterno"
                    name="apellido_paterno"
                    placeholder="Apellido paterno"
                />
                <InputError :message="errors.apellido_paterno" />
            </div>

            <div class="grid gap-4">
                <Label for="apellido_materno">Apellido materno</Label>
                <Input
                    id="apellido_materno"
                    type="text"
                    required
                    :tabindex="4"
                    autocomplete="apellido_materno"
                    name="apellido_materno"
                    placeholder="Apellido materno"
                />
                <InputError :message="errors.apellido_materno" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="5"
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Contraseña</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="6"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Contraseña"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirmar contraseña</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="7"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirmar contraseña"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="8"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create account
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            ¿Ya tienes una cuenta?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="9"
                >Inicia sesion</TextLink
            >
        </div>
    </Form>
</template>
