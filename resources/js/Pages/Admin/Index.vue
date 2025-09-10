<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// PrimeVue
import Card from 'primevue/card'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Tag from 'primevue/tag'

// Icons
import {
    Shield, Settings, Users, UserPlus, Wallet2, CreditCard, FileBarChart, LayoutDashboard, Activity
} from 'lucide-vue-next'

const props = defineProps({
    kpis: {
        type: Object,
        default: () => ({
            usuarios: 0,
            asesores: 0,
            clientes: 0,
            cuentas: 0,
            creditos: 0,
        }),
    },
})

const nf = (n) => new Intl.NumberFormat('es-GT').format(Number(n || 0))

const tiles = computed(() => ([
    { label: 'Usuarios', value: nf(props.kpis.usuarios), icon: Users, cls: 'from-[#0b3a5b] to-[#1971c2]' },
    { label: 'Asesores', value: nf(props.kpis.asesores), icon: Activity, cls: 'from-[#1971c2] to-[#22b8cf]' },
    { label: 'Clientes', value: nf(props.kpis.clientes), icon: Settings, cls: 'from-[#22b8cf] to-[#6ee7b7]' },
    { label: 'Cuentas', value: nf(props.kpis.cuentas), icon: Wallet2, cls: 'from-[#0ea5e9] to-[#22b8cf]' },
    { label: 'Créditos', value: nf(props.kpis.creditos), icon: CreditCard, cls: 'from-[#22b8cf] to-[#0b3a5b]' },
]))
</script>

<template>

    <Head title="Administración" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Shield class="w-5 h-5 text-gray-600" />
                <h2 class="text-xl font-semibold text-gray-800">Administración</h2>
            </div>
        </template>

        <!-- Hero -->
        <div class="rounded-2xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-[#0b3a5b] via-[#1971c2] to-[#22b8cf] text-white">
                <div class="px-5 p-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <Settings class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-lg font-semibold leading-tight">Panel de Administración</div>
                            <div class="text-xs/5 text-white/80">Gestión centralizada de usuarios y accesos del portal
                                CHN</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link :href="route('admin.users.index')">
                        <Button class="bg-white/10 hover:bg-white/20 border-0 text-white">Gestionar usuarios</Button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
            <Card v-for="t in tiles" :key="t.label" class="rounded-2xl shadow-sm">
                <template #content>
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl text-white flex items-center justify-center bg-gradient-to-br p-3"
                            :class="t.cls">
                            <component :is="t.icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-2xl font-semibold text-gray-900 leading-tight">{{ t.value }}</div>
                            <div class="text-xs text-gray-500">{{ t.label }}</div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Acciones rápidas + Atajos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <Card class="rounded-2xl shadow-sm lg:col-span-2">
                <template #title>
                    <div class="flex items-center gap-2">
                        <Users class="w-4 h-4 text-gray-600" />
                        <span>Acciones rápidas</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                        <Link :href="route('admin.users.index')">
                        <div
                            class="group border border-gray-200 rounded-2xl p-4 bg-white hover:bg-gray-50 cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center p-3">
                                    <Users class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">Gestionar usuarios</div>
                                    <div class="text-xs text-gray-500">Crear, editar, activar / desactivar</div>
                                </div>
                            </div>
                        </div>
                        </Link>

                        <Link :href="route('admin.users.index')" preserve-state>
                        <div
                            class="group border border-gray-200 rounded-2xl p-4 bg-white hover:bg-gray-50 cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center p-3">
                                    <UserPlus class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">Nuevo usuario</div>
                                    <div class="text-xs text-gray-500">Alta inmediata</div>
                                </div>
                            </div>
                        </div>
                        </Link>

                        <Link :href="route('reportes.index')">
                        <div
                            class="group border border-gray-200 rounded-2xl p-4 bg-white hover:bg-gray-50 cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center p-3">
                                    <FileBarChart class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">Reportes</div>
                                    <div class="text-xs text-gray-500">Créditos y cuentas</div>
                                </div>
                            </div>
                        </div>
                        </Link>
                    </div>
                </template>
            </Card>

            <Card class="rounded-2xl shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <LayoutDashboard class="w-4 h-4 text-gray-600" />
                        <span>Atajos del sistema</span>
                    </div>
                </template>
                <template #content>
                    <div class="space-y-2">
                        <Link :href="route('dashboard')">
                        <div
                            class="my-2 flex items-center justify-between border border-gray-200 rounded-xl px-3 py-2 hover:bg-gray-50">
                            <div class="text-sm text-gray-700">Dashboard general</div>
                            <Tag class="bg-gray-100 text-gray-700 border-gray-200">Inicio</Tag>
                        </div>
                        </Link>
                        <Link :href="route('mis.asignaciones')">
                        <div
                            class="my-2 flex items-center justify-between border border-gray-200 rounded-xl px-3 py-2 hover:bg-gray-50">
                            <div class="text-sm text-gray-700">Mis asignaciones</div>
                            <Tag class="bg-blue-50 text-blue-700 border-blue-200">Asesor</Tag>
                        </div>
                        </Link>
                        <Link :href="route('creditos.index')">
                        <div
                            class="my-2 flex items-center justify-between border border-gray-200 rounded-xl px-3 py-2 hover:bg-gray-50">
                            <div class="text-sm text-gray-700">Créditos</div>
                            <Tag class="bg-cyan-50 text-cyan-700 border-cyan-200">Operativo</Tag>
                        </div>
                        </Link>
                        <Link :href="route('cuentas.index')">
                        <div
                            class="my-2 flex items-center justify-between border border-gray-200 rounded-xl px-3 py-2 hover:bg-gray-50">
                            <div class="text-sm text-gray-700">Cuentas</div>
                            <Tag class="bg-emerald-50 text-emerald-700 border-emerald-200">Operativo</Tag>
                        </div>
                        </Link>
                    </div>
                </template>
            </Card>
        </div>
        <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4">
            <div class="flex items-start gap-3">
                <div class="h-9 w-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                    <Shield class="w-4 h-4" />
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-800">Permisos de administrador</div>
                    <p class="text-sm text-gray-600">
                        Los usuarios con rol <span class="font-semibold">Admin</span> pueden acceder a todo el portal
                        independientemente del área o módulos asignados. Usá este panel para gestionar altas, bajas y
                        roles.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
