import { reactive } from 'vue';

type OpcionesConfirmacion = {
    titulo?: string;
    descripcion?: string;
    confirmarTexto?: string;
    destructivo?: boolean;
};

type EstadoConfirmacion = Required<OpcionesConfirmacion> & {
    open: boolean;
    resolver: ((valor: boolean) => void) | null;
};

const state = reactive<EstadoConfirmacion>({
    open: false,
    titulo: '',
    descripcion: '',
    confirmarTexto: 'Confirmar',
    destructivo: false,
    resolver: null,
});

export function useConfirmState() {
    return state;
}

export function useConfirm() {
    function confirmar({
        titulo = '¿Estás seguro?',
        descripcion = '',
        confirmarTexto = 'Confirmar',
        destructivo = false,
    }: OpcionesConfirmacion = {}): Promise<boolean> {
        return new Promise((resolve) => {
            Object.assign(state, {
                open: true,
                titulo,
                descripcion,
                confirmarTexto,
                destructivo,
                resolver: resolve,
            });
        });
    }

    return { confirmar };
}
