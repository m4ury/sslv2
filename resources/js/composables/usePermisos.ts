import { usePage } from '@inertiajs/vue3';

type Compartido = { auth?: { can?: Record<string, boolean> } };

export function usePermisos() {
    const page = usePage();

    function puede(permiso: string): boolean {
        return !!(page.props as unknown as Compartido).auth?.can?.[permiso];
    }

    return { puede };
}