<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CuentasRecientes from '@/Components/Cuentas/CuentasRecientes.vue'
import axios from 'axios'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Toast from 'primevue/toast'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'
import Chip from 'primevue/chip'
import { CreditCard, UserRound, CalendarDays, Save, RotateCcw, ShieldCheck, RefreshCw, Search } from 'lucide-vue-next'
const props = defineProps({
    clientes: { type: Array, default: () => [] },
    tiposCuenta: { type: Array, default: () => [] },
    asesores: { type: Array, default: () => [] },
    storeUrl: { type: String, default: '/cuentas' },
    apiClientesUrl: { type: String, default: '/obtener/clientes' },
    apiTiposCuentaUrl: { type: String, default: '/tipos-cuenta' },
    apiAsesoresUrl: { type: String, default: '/asesores' },
    apiRecentUrl: { type: String, default: '/cuentas/recent' },
})

const toast = useToast()
const form = reactive({
    cliente_id: null,
    tipo_cuenta_id: null,
    asesor_id: null,
    fecha_apertura: null,
})

const catalogos = reactive({
    clientes: props.clientes || [],
    tiposCuenta: props.tiposCuenta || [],
    asesores: props.asesores || []
})

const loadingCatalogs = ref(false)
const submitting = ref(false)
const today = new Date()

function validate() {
    const missing = []
    if (!form.cliente_id) missing.push('Cliente')
    if (!form.tipo_cuenta_id) missing.push('Tipo de cuenta')
    if (!form.asesor_id) missing.push('Asesor')
    if (!form.fecha_apertura) missing.push('Fecha de apertura')

    if (missing.length) {
        toast.add({
            severity: 'warn',
            summary: 'Campos requeridos',
            detail: `Falta completar: ${missing.join(', ')}`,
            life: 4000
        })
        return false
    }
    return true
}

async function loadCatalogsIfNeeded() {
    if (catalogos.clientes.length && catalogos.tiposCuenta.length && catalogos.asesores.length) return
    loadingCatalogs.value = true
    try {
        const [cRes, tRes, aRes] = await Promise.all([
            axios.get(props.apiClientesUrl),
            axios.get(props.apiTiposCuentaUrl),
            axios.get(props.apiAsesoresUrl)
        ])
        catalogos.clientes = cRes.data || []
        catalogos.tiposCuenta = tRes.data || []
        catalogos.asesores = aRes.data || []
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Error cargando catálogos',
            detail: 'No se pudieron cargar los datos necesarios.',
            life: 4500
        })
    } finally {
        loadingCatalogs.value = false
    }
}

const resumen = computed(() => {
    const cliente = catalogos.clientes.find(c => c.id === form.cliente_id)
    const tipo = catalogos.tiposCuenta.find(t => t.id === form.tipo_cuenta_id)
    const asesor = catalogos.asesores.find(a => a.id === form.asesor_id)
    return {
        cliente: cliente?.nombre || cliente?.razon_social || '—',
        tipo: tipo?.nombre || '—',
        asesor: asesor?.nombre || '—',
        fecha: form.fecha_apertura ? new Date(form.fecha_apertura).toLocaleDateString() : '—'
    }
})

function resetForm() {
    form.cliente_id = null
    form.tipo_cuenta_id = null
    form.asesor_id = null
    form.fecha_apertura = null
}

async function submit() {
    if (!validate()) return
    submitting.value = true
    try {
        const payload = {
            cliente_id: form.cliente_id,
            tipo_cuenta_id: form.tipo_cuenta_id,
            asesor_id: form.asesor_id,
            fecha_apertura: form.fecha_apertura
                ? new Date(form.fecha_apertura).toISOString().slice(0, 10)
                : null
        }

        await axios.post(props.storeUrl, payload)

        toast.add({
            severity: 'success',
            summary: 'Cuenta creada',
            detail: 'La cuenta se ha guardado correctamente.',
            life: 3000
        })
        resetForm()
        await fetchRecent()
    } catch (e) {
        const detail = e?.response?.data?.message || 'Revisa los datos e intenta de nuevo.'
        toast.add({
            severity: 'error',
            summary: 'Error al guardar',
            detail,
            life: 4500
        })
    } finally {
        submitting.value = false
    }
}

onMounted(async () => {
    await loadCatalogsIfNeeded()
})
</script>

<template>
    <AuthenticatedLayout>
        <Toast />

        <div class="p-6 space-y-6">
            <!-- Encabezado -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <CreditCard class="w-6 h-6" />
                    <div>
                        <h1 class="text-2xl font-bold leading-tight">Nueva cuenta</h1>
                        <p class="text-sm text-surface-500">Completa la información para registrar una cuenta</p>
                    </div>
                </div>
                <Tag value="Seguro" severity="success" class="hidden sm:inline-flex">
                    <template #icon>
                        <ShieldCheck class="w-4 h-4 mr-1" />
                    </template>
                </Tag>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Formulario principal -->
                <Card class="lg:col-span-2 shadow-2 rounded-2xl">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <UserRound class="w-5 h-5" />
                            <span>Datos de la cuenta</span>
                        </div>
                    </template>

                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Cliente</label>
                                <div v-if="loadingCatalogs">
                                    <Skeleton height="2.8rem" class="rounded-lg" />
                                </div>
                                <Dropdown v-else v-model="form.cliente_id" :options="catalogos.clientes"
                                    optionLabel="nombre_cliente" optionValue="id" placeholder="Selecciona un cliente"
                                    class="w-full" :filter="true" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Tipo de cuenta</label>
                                <div v-if="loadingCatalogs">
                                    <Skeleton height="2.8rem" class="rounded-lg" />
                                </div>
                                <Dropdown v-else v-model="form.tipo_cuenta_id" :options="catalogos.tiposCuenta"
                                    optionLabel="nombre" optionValue="id" placeholder="Selecciona el tipo"
                                    class="w-full" :filter="true" />
                            </div>

                            <!-- Asesor -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Asesor</label>
                                <div v-if="loadingCatalogs">
                                    <Skeleton height="2.8rem" class="rounded-lg" />
                                </div>
                                <Dropdown v-else v-model="form.asesor_id" :options="catalogos.asesores"
                                    optionLabel="nombre" optionValue="id" placeholder="Selecciona un asesor"
                                    class="w-full" :filter="true" />
                            </div>

                            <!-- Fecha de apertura -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Fecha de apertura</label>
                                <Calendar v-model="form.fecha_apertura" :maxDate="today" dateFormat="yy-mm-dd" showIcon
                                    iconDisplay="input" class="w-full">
                                    <template #inputicon>
                                        <CalendarDays class="w-4 h-4" />
                                    </template>
                                </Calendar>
                            </div>
                        </div>

                        <Divider />

                        <div class="flex flex-col sm:flex-row gap-3 justify-end">
                            <Button type="button" outlined severity="secondary" class="w-full sm:w-auto button-full"
                                @click="resetForm">
                                <template #icon>
                                    <RotateCcw class="w-4 h-4" />
                                </template>
                                Limpiar
                            </Button>

                            <Button type="button" class="w-full sm:w-auto button-full" :loading="submitting" @click="submit">
                                <template #icon>
                                    <Save class="w-4 h-4" />
                                </template>
                                Guardar cuenta
                            </Button>
                        </div>
                    </template>
                </Card>

                <!-- Resumen / Ayuda -->
                <div class="space-y-5">
                    <Card class="shadow-2 rounded-2xl">
                        <template #title>Resumen</template>
                        <template #content>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <UserRound class="w-4 h-4" />
                                    <span class="text-sm"><b>Cliente:</b> {{ resumen.cliente }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <CreditCard class="w-4 h-4" />
                                    <span class="text-sm"><b>Tipo:</b> {{ resumen.tipo }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserRound class="w-4 h-4" />
                                    <span class="text-sm"><b>Asesor:</b> {{ resumen.asesor }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <CalendarDays class="w-4 h-4" />
                                    <span class="text-sm"><b>Apertura:</b> {{ resumen.fecha }}</span>
                                </div>
                                <div class="pt-2">
                                    <Chip label="Validación rápida" class="mb-2" />
                                    <ul class="text-xs list-disc pl-5 space-y-1 text-surface-500">
                                        <li>Todos los campos son obligatorios.</li>
                                        <li>La fecha no debe ser futura.</li>
                                    </ul>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
            <CuentasRecientes />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.p-card) {
    border-radius: 1rem;
}

.button-full{
    width: 100% !important;
}
</style>
