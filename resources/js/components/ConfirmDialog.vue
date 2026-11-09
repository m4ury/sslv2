<script setup>
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';
import { useConfirmState } from '@/composables/useConfirm';

const state = useConfirmState();

function responder(valor) {
    state.resolver?.(valor);
    state.resolver = null;
    state.open = false;
}
</script>

<template>
    <AlertDialog :open="state.open" @update:open="(abierto) => !abierto && responder(false)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ state.titulo }}</AlertDialogTitle>
                <AlertDialogDescription v-if="state.descripcion">
                    {{ state.descripcion }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="responder(false)">Cancelar</AlertDialogCancel>
                <AlertDialogAction
                    :class="state.destructivo ? buttonVariants({ variant: 'destructive' }) : ''"
                    @click="responder(true)"
                >
                    {{ state.confirmarTexto }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
