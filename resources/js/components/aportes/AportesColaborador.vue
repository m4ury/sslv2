<script setup>
import { useForm } from '@inertiajs/vue3';
import { Loader2, Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { fechaLocal, formatCLP, formatDate } from '@/config/ficha';

const props = defineProps({
    colaborador: { type: Object, required: true },
    aportes: { type: Array, default: () => [] },
    medios: { type: Array, default: () => [] },
});

/* ---------- Totales (solo aportes vigentes) ---------- */

const vigentes = computed(() => props.aportes.filter((a) => !a.anulado_at));
const total = computed(() => vigentes.value.reduce((s, a) => s + a.monto, 0));

/* ---------- Registrar aporte ---------- */

const registrando = ref(false);

const form = useForm({
    fecha_aporte: '',
    monto: '',
    medio_pago: 'Efectivo',
    numero_comprobante: '',
    observaciones: '',
});

function abrirRegistro() {
    form.defaults({
        fecha_aporte: fechaLocal(),
        monto: '',
        medio_pago: 'Efectivo',
        numero_comprobante: '',
        observaciones: '',
    });
    form.reset();
    form.clearErrors();
    registrando.value = true;
}

function guardar() {
    form.post(`/colaboradores/${props.colaborador.id}/aportes`, {
        preserveScroll: true,
        onSuccess: () => {
            registrando.value = false;
        },
    });
}

/* ---------- Anular aporte ---------- */

const anulandoId = ref(null);
const aporteAnulando = computed(
    () => props.aportes.find((a) => a.id === anulandoId.value) ?? null,
);
const anularForm = useForm({ motivo: '' });

function abrirAnulacion(aporte) {
    anularForm.reset();
    anularForm.clearErrors();
    anulandoId.value = aporte.id;
}

function confirmarAnulacion() {
    anularForm.post(`/aportes/${anulandoId.value}/anular`, {
        preserveScroll: true,
        onSuccess: () => {
            anulandoId.value = null;
        },
    });
}

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive';
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-start justify-between gap-4">
            <div class="flex flex-col gap-1.5">
                <CardTitle>Aportes voluntarios</CardTitle>
                <CardDescription>
                    Total aportado:
                    <span class="font-medium text-foreground">{{ formatCLP(total) }}</span>
                    · {{ vigentes.length }}
                    {{ vigentes.length === 1 ? 'aporte' : 'aportes' }}
                </CardDescription>
            </div>
            <Button size="sm" @click="abrirRegistro">
                <Plus class="size-4" />
                Registrar aporte
            </Button>
        </CardHeader>

        <CardContent>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Fecha</TableHead>
                            <TableHead class="text-right">Monto</TableHead>
                            <TableHead>Medio</TableHead>
                            <TableHead>Comprobante</TableHead>
                            <TableHead>Registrado por</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="a in aportes"
                            :key="a.id"
                            :class="{ 'opacity-60': a.anulado_at }"
                        >
                            <TableCell>{{ formatDate(a.fecha_aporte) }}</TableCell>
                            <TableCell
                                class="text-right font-medium"
                                :class="{ 'line-through': a.anulado_at }"
                            >
                                {{ formatCLP(a.monto) }}
                            </TableCell>
                            <TableCell>{{ a.medio_pago ?? '—' }}</TableCell>
                            <TableCell>{{ a.numero_comprobante ?? '—' }}</TableCell>
                            <TableCell>{{ a.usuario?.name ?? '—' }}</TableCell>
                            <TableCell>
                                <div class="flex flex-col gap-1">
                                    <Badge
                                        :variant="a.anulado_at ? 'destructive' : 'secondary'"
                                        class="self-start"
                                    >
                                        {{ a.anulado_at ? 'Anulado' : 'Vigente' }}
                                    </Badge>
                                    <span
                                        v-if="a.anulado_at"
                                        class="max-w-xs text-xs whitespace-normal text-muted-foreground"
                                    >
                                        {{ a.anulador?.name ?? '—' }}: {{ a.motivo_anulacion }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end">
                                    <Button
                                        v-if="!a.anulado_at"
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive hover:text-destructive"
                                        @click="abrirAnulacion(a)"
                                    >
                                        Anular
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="aportes.length === 0">
                            <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
                                Este colaborador aún no registra aportes.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </CardContent>
    </Card>

    <!-- Registrar aporte -->
    <Dialog v-model:open="registrando">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Registrar aporte</DialogTitle>
                <DialogDescription>
                    Los campos marcados con
                    <span class="text-destructive">*</span> son obligatorios.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-6" @submit.prevent="guardar">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <Label for="fecha_aporte">
                            Fecha <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="fecha_aporte"
                            v-model="form.fecha_aporte"
                            type="date"
                            :max="fechaLocal()"
                            :aria-invalid="!!form.errors.fecha_aporte"
                            required
                        />
                        <InputError :message="form.errors.fecha_aporte" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="monto">
                            Monto <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="monto"
                            v-model="form.monto"
                            type="number"
                            step="1"
                            min="1"
                            :aria-invalid="!!form.errors.monto"
                            required
                        />
                        <InputError :message="form.errors.monto" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="medio_pago">
                            Medio de pago <span class="text-destructive">*</span>
                        </Label>
                        <select
                            id="medio_pago"
                            v-model="form.medio_pago"
                            :class="selectClass"
                            :aria-invalid="!!form.errors.medio_pago"
                            required
                        >
                            <option v-for="medio in medios" :key="medio" :value="medio">
                                {{ medio }}
                            </option>
                        </select>
                        <InputError :message="form.errors.medio_pago" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="numero_comprobante">
                            N° de comprobante
                            <span
                                v-if="form.medio_pago !== 'Efectivo'"
                                class="text-destructive"
                                >*</span
                            >
                        </Label>
                        <Input
                            id="numero_comprobante"
                            v-model="form.numero_comprobante"
                            type="text"
                            :aria-invalid="!!form.errors.numero_comprobante"
                            :required="form.medio_pago !== 'Efectivo'"
                        />
                        <InputError :message="form.errors.numero_comprobante" />
                    </div>

                    <div class="flex flex-col gap-2 sm:col-span-2">
                        <Label for="observaciones">Observaciones</Label>
                        <Textarea
                            id="observaciones"
                            v-model="form.observaciones"
                            rows="3"
                            :aria-invalid="!!form.errors.observaciones"
                        />
                        <InputError :message="form.errors.observaciones" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="registrando = false">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        Registrar aporte
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Anular aporte -->
    <Dialog
        :open="!!aporteAnulando"
        @update:open="(abierto) => !abierto && (anulandoId = null)"
    >
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Anular aporte</DialogTitle>
                <DialogDescription>
                    {{ formatCLP(aporteAnulando?.monto) }} ·
                    {{ formatDate(aporteAnulando?.fecha_aporte) }}. El aporte quedará
                    en el historial como anulado y dejará de sumar al total.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-4" @submit.prevent="confirmarAnulacion">
                <div class="flex flex-col gap-2">
                    <Label for="motivo">
                        Motivo <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="motivo"
                        v-model="anularForm.motivo"
                        type="text"
                        :aria-invalid="!!anularForm.errors.motivo"
                        required
                    />
                    <InputError :message="anularForm.errors.motivo" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="anulandoId = null">
                        Cancelar
                    </Button>
                    <Button type="submit" variant="destructive" :disabled="anularForm.processing">
                        <Loader2 v-if="anularForm.processing" class="size-4 animate-spin" />
                        Anular aporte
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
