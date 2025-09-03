<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Toast from 'primevue/toast'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Divider from 'primevue/divider'

import { Wallet2, Users, CreditCard, BarChart3, ArrowRight, CalendarDays, AlertTriangle, Plus } from 'lucide-vue-next'

const props = defineProps({
    stats: Object,
    upcomingVenc: Array,
    pendingAmort: Array,
    recentClientes: Array,
    isAdmin: Boolean,
    isAsesor: Boolean,
})

const page = usePage()
const ab = page.props.abilities || {}
const user = page.props.auth.user || {}

const money = (n) => new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(Number(n || 0))
const kpis = computed(() => ([
    { label: 'Clientes', value: props.stats?.clientes ?? 0, icon: Users, to: 'clientes.index', show: ab.canClientes || props.isAdmin },
    { label: 'Cuentas', value: props.stats?.cuentas ?? 0, icon: Wallet2, to: 'cuentas.index', show: ab.canClientes || props.isAdmin},
    { label: 'Créditos', value: props.stats?.creditos ?? 0, icon: CreditCard, to: 'creditos.index', show: true },
    { label: 'Monto', value: money(props.stats?.montoTotal ?? 0), icon: BarChart3, to: 'creditos.index', show: true },
]))
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
                <div class="flex gap-2">
                    <Link :href="route('mis.asignaciones')">
                    <Button outlined size="small">Mis asignaciones</Button>
                    </Link>
                    <Link :href="route('creditos.create')">
                    <Button size="small" class="bg-blue-600 hover:bg-blue-700 text-white">
                        <Plus class="w-4 h-4 mr-1" /> Nuevo crédito
                    </Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <Card class="rounded-2xl shadow-sm">
                <template #content>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-500">Bienvenido,</p>
                            <h1 class="text-2xl font-semibold text-gray-800">{{ user?.name }}</h1>
                            <p class="text-sm text-gray-500 mt-1">
                                Rol: <span class="font-medium">{{ props.isAdmin ? 'Administrador' : (props.isAsesor ?
                                    'Asesor' : 'Usuario') }}</span>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Tag v-if="ab.mod_credit_reports" value="Reportes de créditos"
                                class="bg-indigo-50 text-indigo-700 border-indigo-200" />
                            <Tag v-if="ab.mod_accounts_report" value="Reportería de cuentas"
                                class="bg-emerald-50 text-emerald-700 border-emerald-200" />
                        </div>
                    </div>
                </template>
            </Card>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card v-for="(k, i) in kpis.filter(x => x.show)" :key="i" class="rounded-2xl shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500">{{ k.label }}</div>
                                <div class="text-2xl font-semibold text-gray-800 mt-1">{{ k.value }}</div>
                            </div>
                            <component :is="k.icon" class="w-6 h-6 text-gray-500" />
                        </div>
                        <Divider />
                        <Link :href="route(k.to)"
                            class="text-sm text-blue-700 hover:underline inline-flex items-center">
                        Ver módulo
                        <ArrowRight class="w-4 h-4 ml-1" />
                        </Link>
                    </template>
                </Card>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <Card class="rounded-2xl shadow-sm lg:col-span-2">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <CalendarDays class="w-4 h-4 text-gray-600" />
                            <span>Próximos vencimientos (30 días)</span>
                        </div>
                    </template>
                    <template #content>
                        <DataTable :value="upcomingVenc" dataKey="id" paginator :rows="5" class="text-sm">
                            <Column field="id" header="Crédito" style="width: 90px" />
                            <Column field="cliente" header="Cliente" />
                            <Column field="fecha_vencimiento" header="Vencimiento" style="width: 140px" />
                            <Column field="monto" header="Monto" style="width: 160px">
                                <template #body="{ data }">{{ new
                                    Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(data.monto
                                        || 0)
                                    }}</template>
                            </Column>
                            <Column header="Acciones" style="width: 120px">
                                <template #body="{ data }">
                                    <Link :href="route('creditos.edit', data.id)">
                                    <Button size="small" outlined>Gestionar</Button>
                                    </Link>
                                </template>
                            </Column>
                        </DataTable>

                        <div v-if="upcomingVenc.length === 0" class="text-sm text-gray-500 py-4">
                            No hay vencimientos próximos. 🎉
                        </div>
                    </template>
                </Card>
                <Card class="rounded-2xl shadow-sm">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <AlertTriangle class="w-4 h-4 text-amber-600" />
                            <span>Pendientes (30 días)</span>
                        </div>
                    </template>
                    <template #content>
                        <ul class="space-y-3">
                            <li v-for="a in pendingAmort" :key="a.id" class="flex items-center justify-between">
                                <div class="text-sm">
                                    Amortización <span class="font-medium">#{{ a.id }}</span>
                                    <span class="text-gray-500"> · Crédito {{ a.credito_id }}</span>
                                </div>
                                <Tag :value="a.fecha_pago" class="bg-amber-50 text-amber-700 border-amber-200" />
                            </li>
                        </ul>
                        <div v-if="pendingAmort.length === 0" class="text-sm text-gray-500">Sin pendientes inmediatos.
                        </div>

                        <Divider class="my-4" />
                        <div class="space-y-2">
                            <Link :href="route('mis.asignaciones')">
                            <Button class="w-full" outlined>Ir a Mis asignaciones</Button>
                            </Link>
                            <Link v-if="props.isAdmin && (ab.mod_credit_reports || ab.mod_accounts_report)"
                                :href="route('reportes.index')">
                            <Button class="w-full" outlined>Centro de reportes</Button>
                            </Link>
                        </div>
                    </template>
                </Card>
            </div>
            <Card v-if="ab.canClientes || props.isAdmin" class="rounded-2xl shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <Users class="w-4 h-4 text-gray-600" />
                        <span>Últimos clientes</span>
                    </div>
                </template>
                <template #content>
                    <DataTable :value="recentClientes" dataKey="id" class="text-sm" paginator :rows="6">
                        <Column field="nombre_cliente" header="Cliente" />
                        <Column field="nit" header="NIT" style="width: 160px" />
                        <Column field="telefono" header="Teléfono" style="width: 140px" />
                        <Column field="email" header="Email" />
                        <Column field="created_at" header="Alta" style="width: 120px" />
                    </DataTable>
                    <div class="mt-4 flex gap-2">
                        <Link :href="route('clientes.create')">
                        <Button class="bg-emerald-600 hover:bg-emerald-700 text-white">
                            Registrar cliente
                        </Button>
                        </Link>
                        <Link :href="route('clientes.index')">
                        <Button outlined>Ver módulo</Button>
                        </Link>
                    </div>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
