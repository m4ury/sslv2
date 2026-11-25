<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-linear-to-br from-background to-sidebar-background p-6 text-foreground lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-lg border border-border bg-card/50 px-5 py-1.5 text-sm leading-normal font-medium transition-colors hover:bg-accent hover:text-accent-foreground backdrop-blur-sm"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-lg border border-transparent px-5 py-1.5 text-sm leading-normal font-medium transition-colors hover:bg-accent/50 dark:text-[#EDEDEC]"
                    >
                        Inicia sesión
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-block rounded-lg border border-border bg-card/50 px-5 py-1.5 text-sm leading-normal font-medium transition-colors hover:bg-accent hover:text-accent-foreground backdrop-blur-sm"
                    >
                        Regístrate
                    </Link>
                </template>
            </nav>
        </header>

        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="flex w-full max-w-[335px] flex-col overflow-hidden rounded-2xl border border-border bg-card/60 shadow-xl backdrop-blur-md animate-in fade-in zoom-in-95 duration-500 lg:max-w-4xl lg:flex-row"
            >
                <!-- Lado Izquierdo: Visual/Logo -->
                <div class="relative flex flex-col items-center justify-center bg-sidebar/10 p-8 lg:w-2/5">
                    <div class="relative">
                        <!-- Círculos decorativos en el fondo -->
                        <div class="absolute inset-0 -m-4 animate-pulse rounded-full bg-primary/20 blur-2xl"></div>
                        <AppLogoIcon class="relative size-68 object-contain transition-transform duration-500 hover:scale-110 lg:size-78" />
                    </div>
                    <div class="mt-2 text-center">
                        <p class="text-xs font-semibold tracking-widest text-muted-foreground uppercase">Fundada el</p>
                        <p class="mt-1 text-sm font-medium text-foreground">20 de agosto de 2009</p>
                    </div>
                </div>

                <!-- Lado Derecho: Contenido -->
                <div
                    class="flex flex-1 flex-col justify-center p-8 lg:p-16"
                >
                    <h1 class="text-3xl font-extrabold tracking-tight lg:text-4xl">
                        Sueños Sin Límites
                    </h1>

                    <div class="mt-8 space-y-6">
                        <p class="text-lg italic leading-relaxed text-foreground/80">
                            "Nunca hay un final sino muchos caminos para llegar, vive sin límites y aprende a soñar"
                        </p>

                        <div class="h-1 w-12 rounded-full bg-primary/60"></div>

                        <p class="text-base text-muted-foreground">
                            Bienvenido a Sueños Sin Límites, una plataforma integral diseñada para gestionar nuestra comunidad,
                            socios y pacientes de manera eficiente y moderna.
                        </p>

                        <div class="flex flex-wrap gap-4 pt-4">
                             <Link
                                v-if="!$page.props.auth.user"
                                :href="login()"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-8 text-sm font-bold text-primary-foreground shadow-lg transition-all active:scale-95 hover:bg-primary/90 hover:shadow-primary/20"
                            >
                                Comenzar ahora
                            </Link>
                            <Link
                                v-else
                                :href="dashboard()"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-8 text-sm font-bold text-primary-foreground shadow-lg transition-all active:scale-95 hover:bg-primary/90 hover:shadow-primary/20"
                            >
                                Ir al Dashboard
                            </Link>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
