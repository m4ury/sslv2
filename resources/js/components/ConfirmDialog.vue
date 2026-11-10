<script setup>
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button, buttonVariants } from '@/components/ui/button';
import { useConfirmState } from '@/composables/useConfirm';

const state = useConfirmState();

function responder(valor) {
    const resolver = state.resolver;
    state.resolver = null;
    state.open = false;
    resolver?.(valor);
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
                <Button type="button" variant="outline" @click="responder(false)">
                    Cancelar
                </Button>
                <Button
                    type="button"
                    :class="state.destructivo ? buttonVariants({ variant: 'destructive' }) : ''"
                    @click="responder(true)"
                >
                    {{ state.confirmarTexto }}
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
