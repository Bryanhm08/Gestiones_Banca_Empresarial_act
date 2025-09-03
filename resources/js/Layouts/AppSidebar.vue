<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard,
    Users,
    Wallet2,
    CreditCard,
    FileBarChart,
    ClipboardList,
    UserPlus,
    Search,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next'

const page = usePage()
const ab = page.props.abilities || {}

const open = ref(true)
onMounted(() => {
    const saved = localStorage.getItem('chn_sidebar_open')
    if (saved !== null) open.value = saved === '1'
})
const toggle = () => {
    open.value = !open.value
    localStorage.setItem('chn_sidebar_open', open.value ? '1' : '0')
}

const q = ref('')
const items = computed(() => {
    return [
        { label: 'Dashboard', icon: LayoutDashboard, routeName: 'dashboard', show: true },
        { label: 'Clientes', icon: Users, routeName: 'clientes.index', show: ab.canViewClientes },
        { label: 'Cuentas', icon: Wallet2, routeName: 'cuentas.index', show: ab.canViewCuentas },

        { label: 'Créditos', icon: CreditCard, routeName: 'creditos.index', show: true },
        { label: 'Nuevo crédito', icon: UserPlus, routeName: 'creditos.create', show: ab.canCreateCredito },

        { label: 'Mis Asignaciones', icon: ClipboardList, routeName: 'mis.asignaciones', show: true },
        { label: 'Reportes', icon: FileBarChart, routeName: 'reportes.index', show: (ab.mod_credit_reports || ab.mod_accounts_report) },
    ].filter(i => i.show)
})
const isActive = (name) => route().current(name + '*')
</script>

<template>
    <aside class="shrink-0 self-stretch" aria-label="CHN Sidebar">
        <div :class="[
            'sticky top-0 min-h-screen',
            'bg-white ring-1 ring-black/5',
            'flex flex-col overflow-hidden transition-all duration-200',
            open ? 'w-64' : 'w-[76px]'
        ]">
            <div class="px-3 pt-3">
                <Link :href="route('dashboard')" class="flex items-center gap-2 rounded-2xl px-2 py-2 hover:bg-gray-50">
                <img src="/favicon.png" alt="CHN" class="h-6 w-6 rounded-md" />
                <span v-if="open" class="font-semibold tracking-wide text-gray-800">CHN · SGBE</span>
                </Link>
            </div>

            <div class="px-3 pt-2">
                <div v-if="open" class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input v-model="q" type="text" placeholder="Buscar"
                        class="w-full h-9 pl-9 pr-3 rounded-xl bg-gray-50 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400" />
                </div>
                <button v-else
                    class="w-full h-9 flex items-center justify-center rounded-xl bg-gray-50 text-gray-500 hover:bg-gray-100"
                    Tooltip.bottom="'Buscar'" type="button">
                    <Search class="w-4 h-4" />
                </button>
            </div>
            <nav class="mt-2 px-2 flex-1 overflow-y-auto">
                <ul class="space-y-1 py-1">
                    <li v-for="it in items" :key="it.routeName">
                        <Link :href="route(it.routeName)" :aria-current="isActive(it.routeName) ? 'page' : undefined"
                            class="group block" Tooltip.bottom="!open ? it.label : null">
                        <div :class="[
                            'flex items-center gap-3 rounded-xl',
                            'px-2 py-2 transition',
                            isActive(it.routeName)
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-gray-700 hover:bg-gray-50'
                        ]">
                            <component :is="it.icon" :class="[
                                'w-4 h-4 shrink-0',
                                isActive(it.routeName) ? 'text-white' : 'text-gray-500 group-hover:text-gray-700'
                            ]" />
                            <span v-if="open" class="text-sm font-medium truncate">
                                {{ it.label }}
                            </span>
                        </div>
                        </Link>
                    </li>
                </ul>
            </nav>

            <div class="mt-auto px-3 pb-3">
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-2 flex items-center gap-3">
                    <button class="w-full rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 p-2"
                        @click="toggle" :aria-label="open ? 'Colapsar sidebar' : 'Expandir sidebar'">
                        <ChevronLeft v-if="open" class="w-4 h-4" />
                        <ChevronRight v-else class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </aside>
</template>

<style scoped></style>
