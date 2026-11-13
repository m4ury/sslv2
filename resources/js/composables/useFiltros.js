import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, reactive, watch } from 'vue';

// Filtros que consultan al servidor con una pequeña espera entre teclas
export function useFiltros(url, iniciales = {}) {
    const filtros = reactive({ ...iniciales });
    let timeout;

    watch(filtros, () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const params = Object.fromEntries(
                Object.entries(filtros).filter(([, v]) => v !== '' && v != null),
            );
            router.get(url, params, { preserveState: true, preserveScroll: true, replace: true });
        }, 300);
    });

    onBeforeUnmount(() => clearTimeout(timeout));

    return filtros;
}