import { createInertiaApp, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

import 'vue-sonner/style.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});

type FlashMensajes = { success?: string | null; error?: string | null };

const MENSAJES_HTTP: Record<number, string> = {
    403: 'No tienes permiso para realizar esta acción.',
    404: 'No se encontró lo que buscabas.',
    419: 'Tu sesión expiró. Recarga la página e inténtalo de nuevo.',
    429: 'Demasiados intentos. Espera un momento e inténtalo de nuevo.',
};

// Mensajes que el servidor envía con ->with('success') o ->with('error')
router.on('success', (event) => {
    const flash = (event.detail.page.props as { flash?: FlashMensajes }).flash;

    if (flash?.success) toast.success(flash.success);
    if (flash?.error) toast.error(flash.error);
});

// Errores de validación: también se ven si el campo no está a la vista
router.on('error', (event) => {
    const primero = Object.values(event.detail.errors ?? {})[0];
    toast.error(primero ?? 'Revisa los campos del formulario.');
});

// Respuestas HTTP que no son de Inertia (403, 404, 419, 500...)
router.on('httpException', (event) => {
    const status = event.detail.response.status;

    // En desarrollo se mantiene la pantalla de error con el detalle
    if (import.meta.env.DEV && status >= 500) return;

    event.preventDefault();
    toast.error(
        MENSAJES_HTTP[status] ??
            'Ocurrió un error en el servidor. Inténtalo nuevamente.',
    );
});

// Fallas de conexión
router.on('networkError', () => {
    toast.error('No se pudo conectar con el servidor. Revisa tu conexión.');
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
