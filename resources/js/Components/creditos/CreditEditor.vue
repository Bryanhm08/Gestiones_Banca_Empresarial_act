<script setup>
import { computed, ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

import PrimaryButton from '@/Components/PrimaryButton.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    credito: {
        type: Object,
        required: true,
    },
    tipos: {
        type: Array,
        required: true,
    },
    garantias: {
        type: Array,
        required: true,
    },
    estadosCatalog: {
        type: Array,
        required: true,
    },
    timeline: {
        type: Array,
        required: true,
    },
    amortizaciones: {
        type: Array,
        required: true,
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
})

// =============== FORM DATOS DEL CRÉDITO ==================

const form = useForm({
    tipo_credito_id: props.credito.tipo_credito_id,
    garantia_id: props.credito.garantia_id,
    monto: props.credito.monto,
    plazo: props.credito.plazo,
    // fecha_concesion / fecha_vencimiento ya no se editan en el pipeline
})

const guardarDatosCredito = () => {
    form.patch(route('creditos.update', props.credito.id), {
        preserveScroll: true,
    })
}

// ================== ETAPAS DEL CRÉDITO ==================

const nuevaEtapaId = ref(null)
const estadosDisponibles = computed(() => props.estadosCatalog ?? [])

const agregarEtapa = () => {
    if (!nuevaEtapaId.value) return

    window.axios
        .post(route('creditos.estado.add', props.credito.id), {
            estado_id: nuevaEtapaId.value,
        })
        .then(() => {
            nuevaEtapaId.value = null
            router.reload({
                only: ['timeline', 'estadosCatalog'],
                preserveScroll: true,
            })
        })
        .catch((error) => {
            console.error('Error al agregar etapa', error)
        })
}

const revertirEtapa = (item) => {
    if (!confirm('¿Deseás eliminar la última etapa registrada?')) return

    window.axios
        .delete(
            route('creditos.estado.remove', {
                id: props.credito.id,
                estadoId: item.id,
            }),
        )
        .then(() => {
            router.reload({
                only: ['timeline', 'estadosCatalog'],
                preserveScroll: true,
            })
        })
        .catch((error) => {
            console.error('Error al eliminar etapa', error)
        })
}

// ================== AMORTIZACIONES ==================

const amortForm = ref({
    fecha_pago: '',
    status: 'Pendiente',
})

const amortErrors = ref({})

const agregarAmortizacion = () => {
    amortErrors.value = {}

    if (!amortForm.value.fecha_pago) {
        amortErrors.value.fecha_pago = ['La fecha de pago es obligatoria.']
        return
    }

    window.axios
        .post(route('creditos.amortizaciones.store', props.credito.id), {
            fecha_pago: amortForm.value.fecha_pago,
            status: amortForm.value.status,
        })
        .then(() => {
            amortForm.value.fecha_pago = ''
            amortForm.value.status = 'Pendiente'

            router.reload({
                only: ['amortizaciones'],
                preserveScroll: true,
            })
        })
        .catch((error) => {
            if (error.response && error.response.status === 422) {
                amortErrors.value = error.response.data.errors || {}
            } else {
                console.error('Error al agregar amortización', error)
            }
        })
}

// Cambiar estado Pagado / Pendiente
const toggleAmortizacionStatus = (amort) => {
    window.axios
        .patch(route('amortizaciones.toggle', amort.id))
        .then(() => {
            router.reload({
                only: ['amortizaciones'],
                preserveScroll: true,
            })
        })
        .catch((error) => {
            console.error('Error al cambiar estado de amortización', error)
        })
}

// Eliminar amortización
const eliminarAmortizacion = (amort) => {
    if (!confirm('¿Deseás eliminar esta amortización?')) return

    window.axios
        .delete(route('amortizaciones.destroy', amort.id))
        .then(() => {
            router.reload({
                only: ['amortizaciones'],
                preserveScroll: true,
            })
        })
        .catch((error) => {
            console.error('Error al eliminar amortización', error)
        })
}
</script>

<template>
    <div class="max-w-6xl mx-auto py-8 space-y-8">
        <!-- Encabezado -->
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">
                    Editar crédito #{{ credito.id }}
                </h1>
                <p class="text-sm text-slate-500">
                    Cliente:
                    <span class="font-medium">{{ credito.cliente }}</span>
                    <span v-if="credito.asesor" class="ml-2 text-xs text-slate-400">
                        · Asesor: {{ credito.asesor }}
                    </span>
                </p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <!-- ================= DATOS DEL CRÉDITO ================= -->
            <section
                class="md:col-span-2 bg-white shadow-sm rounded-2xl p-6 border border-slate-100"
            >
                <h2 class="text-lg font-semibold text-slate-800 mb-4">
                    Datos del crédito
                </h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Cliente (solo lectura) -->
                    <div class="md:col-span-2">
                        <InputLabel value="Cliente" />
                        <div
                            class="mt-1 text-sm font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2"
                        >
                            {{ credito.cliente }}
                        </div>
                    </div>

                    <!-- Tipo de crédito -->
                    <div>
                        <InputLabel for="tipo_credito_id" value="Tipo de crédito" />
                        <select
                            id="tipo_credito_id"
                            v-model="form.tipo_credito_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm"
                        >
                            <option disabled value="">Seleccioná un tipo</option>
                            <option v-for="t in tipos" :key="t.id" :value="t.id">
                                {{ t.nombre }}
                            </option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.tipo_credito_id" />
                    </div>

                    <!-- Garantía -->
                    <div>
                        <InputLabel for="garantia_id" value="Garantía" />
                        <select
                            id="garantia_id"
                            v-model="form.garantia_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm"
                        >
                            <option disabled value="">Seleccioná una garantía</option>
                            <option v-for="g in garantias" :key="g.id" :value="g.id">
                                {{ g.nombre }}
                            </option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.garantia_id" />
                    </div>

                    <!-- Monto -->
                    <div>
                        <InputLabel for="monto" value="Monto" />
                        <TextInput
                            id="monto"
                            v-model="form.monto"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.monto" />
                    </div>

                    <!-- Plazo -->
                    <div>
                        <InputLabel for="plazo" value="Plazo (meses)" />
                        <TextInput
                            id="plazo"
                            v-model="form.plazo"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.plazo" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <PrimaryButton
                        type="button"
                        :disabled="form.processing"
                        @click="guardarDatosCredito"
                    >
                        Guardar cambios
                    </PrimaryButton>
                </div>
            </section>

            <!-- ========= RESUMEN / AMORTIZACIONES ========= -->
            <section
                class="bg-white shadow-sm rounded-2xl p-6 border border-slate-100"
            >
                <h2 class="text-lg font-semibold text-slate-800 mb-3">
                    Resumen
                </h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Monto</dt>
                        <dd class="font-medium">
                            Q
                            {{
                                Number(credito.monto).toLocaleString('es-GT', {
                                    minimumFractionDigits: 2,
                                })
                            }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Plazo</dt>
                        <dd class="font-medium">{{ credito.plazo }} mes(es)</dd>
                    </div>
                </dl>

                <!-- Formulario para agregar amortización -->
                <div class="mt-6 border-t border-slate-200 pt-4">
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">
                        Agregar amortización
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <InputLabel for="fecha_pago" value="Fecha de pago" />
                            <TextInput
                                id="fecha_pago"
                                v-model="amortForm.fecha_pago"
                                type="date"
                                class="mt-1 block w-full"
                            />
                            <p
                                v-if="amortErrors.fecha_pago"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ amortErrors.fecha_pago[0] }}
                            </p>
                        </div>
                        <div>
                            <InputLabel for="status" value="Estado" />
                            <select
                                id="status"
                                v-model="amortForm.status"
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm"
                            >
                                <option value="Pendiente">Pendiente</option>
                                <option value="Pagado">Pagado</option>
                            </select>
                        </div>
                        <div class="flex justify-end">
                            <PrimaryButton type="button" @click="agregarAmortizacion">
                                + Agregar amortización
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">
                        Amortizaciones registradas
                    </h3>
                    <div
                        v-if="amortizaciones.length"
                        class="space-y-1 text-xs max-h-48 overflow-y-auto"
                    >
                        <div
                            v-for="a in amortizaciones"
                            :key="a.id"
                            class="flex items-center justify-between rounded-lg border border-slate-100 px-2 py-1 gap-2"
                        >
                            <div class="flex flex-col">
                                <span>{{ a.fecha_pago }}</span>
                                <span
                                    :class="
                                        a.status === 'Pagado'
                                            ? 'text-emerald-600'
                                            : 'text-amber-600'
                                    "
                                >
                                    {{ a.status }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="text-[11px] px-2 py-1 rounded-lg border border-slate-300 hover:bg-slate-50"
                                    @click="toggleAmortizacionStatus(a)"
                                >
                                    Cambiar estado
                                </button>

                                <button
                                    type="button"
                                    class="text-[11px] px-2 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50"
                                    @click="eliminarAmortizacion(a)"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-xs text-slate-400">
                        No hay amortizaciones registradas.
                    </p>
                </div>
            </section>
        </div>

        <!-- ================= ETAPAS DEL CRÉDITO ================= -->
        <section
            class="bg-white shadow-sm rounded-2xl p-6 border border-slate-100"
        >
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"
            >
                <h2 class="text-lg font-semibold text-slate-800">
                    Etapas del crédito
                </h2>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="nuevaEtapaId"
                        class="rounded-lg border-slate-300 text-sm"
                    >
                        <option disabled value="">Seleccioná etapa</option>
                        <option
                            v-for="e in estadosDisponibles"
                            :key="e.id"
                            :value="e.id"
                            :disabled="e.disabled"
                        >
                            {{ e.nombre }}
                            <span v-if="e.disabled"> (no permitido)</span>
                        </option>
                    </select>

                    <PrimaryButton
                        type="button"
                        class="whitespace-nowrap"
                        :disabled="
                            !nuevaEtapaId ||
                            estadosDisponibles.find(
                                (e) => e.id === nuevaEtapaId && e.disabled,
                            )
                        "
                        @click="agregarEtapa"
                    >
                        + Agregar
                    </PrimaryButton>
                </div>
            </div>

            <!-- Timeline con fechas alineadas -->
            <div v-if="timeline.length" class="pt-4">
                <div class="relative">
                    <!-- Línea base -->
                    <div class="absolute top-3 left-0 right-0 h-0.5 bg-slate-200"></div>

                    <div class="relative flex justify-between gap-4">
                        <div
                            v-for="(item, index) in timeline"
                            :key="item.id"
                            class="flex-1 flex flex-col items-center text-center"
                        >
                            <!-- Punto -->
                            <div
                                class="w-5 h-5 rounded-full border-2 border-slate-500 bg-white flex items-center justify-center z-10"
                            >
                                <div class="w-2 h-2 rounded-full bg-slate-500"></div>
                            </div>

                            <!-- Nombre + fecha alineados -->
                            <div class="mt-2">
                                <div class="text-sm font-medium text-slate-700">
                                    {{ item.estado }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ item.created_at }}
                                </div>
                            </div>

                            <!-- Botón para revertir SOLO la última etapa -->
                            <div v-if="index === timeline.length - 1" class="mt-2">
                                <button
                                    type="button"
                                    class="text-xs text-red-600 hover:underline"
                                    @click="revertirEtapa(item)"
                                >
                                    Revertir etapa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="text-sm text-slate-400">
                Aún no se han registrado etapas para este crédito.
            </p>
        </section>
    </div>
</template>
