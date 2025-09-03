<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, usePage, router, Head} from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import ClientePreview from '@/Components/modals/ClientePreview.vue'
import CuentaPreview from '@/Components/modals/CuentaPreview.vue'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'
import { Users, BadgeDollarSign, Wallet2, Search, Edit, ArrowRight, Plus, CalendarClock, AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
    clientes: { type: Array, default: () => [] },
    cuentas: { type: Array, default: () => [] },
    creditos: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) },
})
const toast = useToast()
const page = usePage()

onMounted(() => {
    const success = page.props.flash?.success || props.flash?.success
    if (success) {
        toast.add({ severity: 'success', summary: '¡Listo!', detail: success, life: 2200 })
    }
})

const money = (n) => new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(Number(n || 0))
const daysTo = (yyyyMMdd) => {
    if (!yyyyMMdd) return null
    const today = new Date(); today.setHours(0, 0, 0, 0)
    const [y, m, d] = yyyyMMdd.split('-').map(Number)
    const target = new Date(y, (m - 1), d)
    const diff = Math.round((target - today) / (1000 * 60 * 60 * 24))
    return diff
}
const vencimientoTag = (yyyyMMdd) => {
    const d = daysTo(yyyyMMdd)
    if (d === null) return { label: '—', cls: 'bg-gray-100 text-gray-700 border-gray-200' }
    if (d < 0) return { label: `Vencido ${Math.abs(d)}d`, cls: 'bg-red-50 text-red-700 border-red-200' }
    if (d <= 15) return { label: `Vence en ${d}d`, cls: 'bg-amber-50 text-amber-700 border-amber-200' }
    return { label: `En ${d}d`, cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' }
}
const qClientes = ref('')
const qCuentas = ref('')
const qCreditos = ref('')
const q = ref('')
const initials = (name = '') =>
    name.split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase()
const uiCuentas = computed(() =>
    props.cuentas.map(c => {
        const clienteName = c.cliente?.nombre_cliente ?? c.cliente ?? '—'
        const clienteNit = c.cliente?.nit ?? '—'
        const tipoNombre = c.tipo?.nombre ?? c.tipo ?? '—'
        return {
            ...c,
            cliente_name: clienteName,
            cliente_nit: clienteNit,
            tipo_nombre: tipoNombre,
            _initials: initials(clienteName),
        }
    })
)
const filteredClientes = computed(() => {
    const t = q.value.trim().toLowerCase()
    if (!t) return props.clientes
    return props.clientes.filter(c =>
        [c.nombre_cliente, c.nit, c.telefono, c.email]
            .map(v => String(v ?? '').toLowerCase())
            .some(v => v.includes(t))
    )
})
const filteredCuentas = computed(() => {
    const term = qCuentas.value.trim().toLowerCase()
    if (!term) return uiCuentas.value
    return uiCuentas.value.filter(c =>
        [
            c.cliente_name,
            c.cliente_nit,
            c.tipo_nombre,
            c.fecha_apertura,
        ]
            .map(v => String(v ?? '').toLowerCase())
            .some(v => v.includes(term))
    )
})


const filteredCreditos = computed(() => {
    const term = qCreditos.value.toLowerCase().trim()
    if (!term) return props.creditos
    return props.creditos.filter(c =>
        [c.id, c.cliente, c.tipo, c.garantia, c.monto, c.plazo, c.fecha_concesion, c.fecha_vencimiento]
            .some(v => String(v ?? '').toLowerCase().includes(term))
    )
})

const kpis = computed(() => ({
    clientes: props.clientes.length,
    cuentas: props.cuentas.length,
    creditos: props.creditos.length,
    montoTotal: props.creditos.reduce((acc, c) => acc + Number(c.monto || 0), 0),
}))
const openCuenta = (row) => {
    selectedCuenta.value = row
    showCuenta.value = true
}
const showCliente = ref(false)
const selectedCliente = ref(null)
const openCliente = (row) => { selectedCliente.value = row; showCliente.value = true }
const goGestionClientes = () => router.visit(route('clientes.index'))
const showCuenta = ref(false)
const selectedCuenta = ref(null)
const goGestionCuentas = () => router.visit(route('cuentas.index'))
</script>

<template>
    <Head title="Asignaciones"/>
    <AuthenticatedLayout>
        <Toast />
        <div class="p-6 space-y-6 max-w-7xl mx-auto">
            <div class="relative overflow-hidden rounded-2xl ring-1 ring-black/5">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-sky-500 to-emerald-500"></div>
                <div class="relative p-8 lg:p-10 text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight flex items-center gap-3">
                                <Wallet2 class="w-8 h-8" />
                                Mis Asignaciones
                            </h1>
                            <p class="mt-2 text-white/90 max-w-2xl">
                                Gestioná tus <strong>Clientes</strong>, <strong>Cuentas</strong> y
                                <strong>Créditos</strong> asignados desde un solo lugar, rápido y con estilo.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <Tag :value="`${kpis.clientes} clientes`"
                                    class="bg-white/10 text-white border-white/20" />
                                <Tag :value="`${kpis.cuentas} cuentas`"
                                    class="bg-white/10 text-white border-white/20" />
                                <Tag :value="`${kpis.creditos} créditos`"
                                    class="bg-white/10 text-white border-white/20" />
                                <Tag :value="`Total ${money(kpis.montoTotal)}`"
                                    class="bg-white/10 text-white border-white/20" />
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('clientes.create')">
                            <Button class="!bg-white !text-gray-900 hover:!bg-white/90">
                                <Plus class="w-4 h-4 mr-2" /> Nuevo cliente
                            </Button>
                            </Link>
                            <Link :href="route('creditos.create')">
                            <Button outlined class="!border-white !text-white hover:!bg-white/10">
                                <Plus class="w-4 h-4 mr-2" /> Nuevo crédito
                            </Button>
                            </Link>
                            <Link :href="route('cuentas.index')">
                            <Button outlined class="!border-white !text-white hover:!bg-white/10">
                                Ir a Cuentas
                                <ArrowRight class="w-4 h-4 ml-2" />
                            </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <Card class="rounded-2xl shadow-sm">
                <template #content>
                    <TabView>
                        <TabPanel header="Clientes asignados">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="relative">
                                    <InputText v-model="qClientes" placeholder="Buscar cliente, NIT, teléfono..."
                                        class="pl-9 w-72" />
                                    <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                                </div>
                                <Link :href="route('clientes.index')">
                                <Button size="small" outlined>
                                    Ir a módulo
                                    <ArrowRight class="w-4 h-4 ml-2" />
                                </Button>
                                </Link>
                            </div>

                            <DataTable :value="filteredClientes" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]"
                                dataKey="id" responsiveLayout="scroll" class="text-sm">
                                <Column field="nombre" header="Cliente" sortable />
                                <Column field="nit" header="NIT" sortable style="width: 160px" />
                                <Column field="telefono" header="Teléfono" style="width: 140px" />
                                <Column field="email" header="Email" />
                                <Column header="Acciones" style="width: 140px">
                                    <template #body="{ data }">
                                        <div class="flex gap-2">
                                            <Button size="small" outlined @click="openCliente(data)">
                                                Ver
                                            </Button>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>

                            <Divider />
                            <div v-if="filteredClientes.length === 0" class="py-6 text-center text-gray-500">
                                Aún no tenés clientes asignados o no hay resultados con ese filtro.
                            </div>
                        </TabPanel>
                        <TabPanel header="Cuentas asignadas">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="relative">
                                    <InputText v-model="qCuentas" placeholder="Buscar cliente, tipo, fecha..."
                                        class="pl-9 w-72" />
                                    <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                                </div>
                                <Link :href="route('cuentas.index')">
                                <Button size="small" outlined>
                                    Ir a módulo
                                    <ArrowRight class="w-4 h-4 ml-2" />
                                </Button>
                                </Link>
                            </div>
                            <DataTable :value="filteredCuentas" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]"
                                dataKey="id" responsiveLayout="scroll" class="text-sm">
                                <Column header="Cliente" sortable sortField="cliente_name">
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="h-8 w-8 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-semibold">
                                                {{ data._initials }}
                                            </span>
                                            <div>
                                                <div class="font-medium text-gray-800">
                                                    {{ data.cliente_name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    NIT: {{ data.cliente_nit }}
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </Column>
                                <Column header="Tipo" sortable sortField="tipo_nombre" style="width: 220px">
                                    <template #body="{ data }">
                                        <Tag class="bg-emerald-50 text-emerald-700 border-emerald-200">
                                            {{ data.tipo_nombre }}
                                        </Tag>
                                    </template>
                                </Column>
                                <Column field="fecha_apertura" header="Apertura" sortable style="width: 140px">
                                    <template #body="{ data }">
                                        <span class="text-gray-800">{{ data.fecha_apertura || '—' }}</span>
                                    </template>
                                </Column>
                                <Column header="Acciones" style="width: 120px">
                                    <template #body="{ data }">
                                        <div class="flex gap-2">
                                            <Button size="small" outlined @click="openCuenta(data)">Ver</Button>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>

                            <Divider />

                            <div v-if="filteredCuentas.length === 0" class="py-6 text-center text-gray-500">
                                No hay cuentas asignadas o no coinciden con el filtro.
                            </div>
                        </TabPanel>

                        <TabPanel header="Créditos asignados">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="relative">
                                    <InputText v-model="qCreditos" placeholder="Buscar cliente, tipo, garantía..."
                                        class="pl-9 w-72" />
                                    <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                                </div>
                                <Link :href="route('creditos.index')">
                                <Button size="small" outlined>
                                    Ir a módulo
                                    <ArrowRight class="w-4 h-4 ml-2" />
                                </Button>
                                </Link>
                            </div>

                            <DataTable :value="filteredCreditos" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]"
                                dataKey="id" responsiveLayout="scroll" class="text-sm">
                                <Column field="id" header="N°" style="width: 80px" sortable />
                                <Column field="cliente" header="Cliente" sortable />
                                <Column header="Tipo" sortable>
                                    <template #body="{ data }">
                                        <Tag :value="data.tipo || '—'"
                                            class="bg-indigo-50 text-indigo-700 border-indigo-200" />
                                    </template>
                                </Column>
                                <Column header="Garantía" sortable>
                                    <template #body="{ data }">
                                        <Tag :value="data.garantia || '—'"
                                            class="bg-emerald-50 text-emerald-700 border-emerald-200" />
                                    </template>
                                </Column>
                                <Column field="monto" header="Monto" sortable style="width: 160px">
                                    <template #body="{ data }"><span class="font-medium">{{ money(data.monto)
                                            }}</span></template>
                                </Column>
                                <Column field="plazo" header="Plazo" sortable style="width: 120px">
                                    <template #body="{ data }">{{ data.plazo }} meses</template>
                                </Column>
                                <Column field="fecha_concesion" header="Concesión" sortable style="width: 130px" />
                                <Column field="fecha_vencimiento" header="Vencimiento" sortable style="width: 160px">
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-2">
                                            <span>{{ data.fecha_vencimiento }}</span>
                                            <Tag :value="vencimientoTag(data.fecha_vencimiento).label"
                                                :class="vencimientoTag(data.fecha_vencimiento).cls" />
                                        </div>
                                    </template>
                                </Column>
                                <Column header="Acciones" style="width: 140px">
                                    <template #body="{ data }">
                                        <div class="flex gap-2">
                                            <Link :href="route('creditos.edit', data.id)">
                                            <Button size="small" outlined>
                                                <Edit class="w-4 h-4 mr-2" /> Editar
                                            </Button>
                                            </Link>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>

                            <Divider />
                            <div v-if="filteredCreditos.length === 0" class="py-6 text-center text-gray-500">
                                No hay créditos asignados aún o el filtro no coincidió.
                            </div>

                            <div class="mt-4 grid md:grid-cols-3 gap-4">
                                <div class="p-4 rounded-xl bg-amber-50 text-amber-700 flex items-start gap-2">
                                    <AlertTriangle class="w-5 h-5 mt-0.5" />
                                    <p class="text-sm">Los créditos que vencen en ≤ 15 días se marcan en amarillo.</p>
                                </div>
                                <div class="p-4 rounded-xl bg-red-50 text-red-700 flex items-start gap-2">
                                    <AlertTriangle class="w-5 h-5 mt-0.5" />
                                    <p class="text-sm">Los créditos vencidos se marcan en rojo para priorizar
                                        seguimiento.</p>
                                </div>
                                <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 flex items-start gap-2">
                                    <CalendarClock class="w-5 h-5 mt-0.5" />
                                    <p class="text-sm">Vencimientos a más de 15 días se muestran como saludables.</p>
                                </div>
                            </div>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>
        <ClientePreview v-model="showCliente" :cliente="selectedCliente" @gestionar="goGestionClientes" />
        <CuentaPreview v-model="showCuenta" :cuenta="selectedCuenta" @gestionar="goGestionCuentas" />
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.p-tabview) {
    --tw-ring-color: transparent;
}

:deep(.p-datatable .p-datatable-header) {
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}
</style>
