<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, Head} from '@inertiajs/vue3'
import { ref, computed } from 'vue'

import Card from 'primevue/card'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Tag from 'primevue/tag'
import { Search, Plus, Edit, UserRound, CalendarDays, BadgeDollarSign, Eye} from 'lucide-vue-next'

const props = defineProps({
    creditos: { type: Array, default: () => [] },
    canCreate: { type: Boolean, default: false },
    isAdmin: { type: Boolean, default: false },
    userId: { type: Number, default: null },
})

const q = ref('')

const money = (n) =>
    new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(Number(n || 0))

const daysTo = (yyyyMMdd) => {
    if (!yyyyMMdd) return null
    const today = new Date(); today.setHours(0, 0, 0, 0)
    const [y, m, d] = yyyyMMdd.split('-').map(Number)
    const target = new Date(y, m - 1, d)
    return Math.round((target - today) / (1000 * 60 * 60 * 24))
}
const vencTag = (yyyyMMdd) => {
    const d = daysTo(yyyyMMdd)
    if (d === null) return { label: '—', cls: 'bg-gray-100 text-gray-600 border-gray-200' }
    if (d < 0) return { label: `Vencido ${Math.abs(d)}d`, cls: 'bg-red-50 text-red-700 border-red-200' }
    if (d <= 15) return { label: `Vence en ${d}d`, cls: 'bg-amber-50 text-amber-700 border-amber-200' }
    return { label: `En ${d}d`, cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' }
}

const filtered = computed(() => {
    const term = q.value.trim().toLowerCase()
    if (!term) return props.creditos
    return props.creditos.filter(c =>
        [
            c.id,
            c.cliente,
            c.tipo,
            c.garantia,
            c.monto,
            c.plazo,
            c.fecha_concesion,
            c.fecha_vencimiento,
            c.asesor,
        ]
            .map(v => String(v ?? '').toLowerCase())
            .some(v => v.includes(term))
    )
})

const isAssignedToMe = (row) => row.asesor_id === props.userId
const canEditRow = (row) => isAssignedToMe(row)

const kpis = computed(() => ({
    total: props.creditos.length,
    monto: props.creditos.reduce((acc, c) => acc + Number(c.monto || 0), 0),
}))
</script>

<template>
    <Head title="Créditos"/>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-800">Créditos</h2>
                <div class="flex items-center gap-2">
                    <div class="relative hidden sm:block">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <InputText v-model="q" placeholder="Buscar crédito, cliente, asesor…" class="pl-9 w-80" />
                    </div>
                    <Link v-if="canCreate" :href="route('creditos.create')">
                    <Button class="bg-blue-600 hover:bg-blue-700 text-white">
                        <Plus class="w-4 h-4 mr-2" /> Nuevo crédito
                    </Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Card class="rounded-2xl shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500">Créditos</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ kpis.total }}</div>
                            </div>
                            <CalendarDays class="w-6 h-6 text-gray-500" />
                        </div>
                    </template>
                </Card>
                <Card class="rounded-2xl shadow-sm">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500">Monto total</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ money(kpis.monto) }}</div>
                            </div>
                            <BadgeDollarSign class="w-6 h-6 text-gray-500" />
                        </div>
                    </template>
                </Card>
            </div>
            <Card class="rounded-2xl shadow-sm">
                <template #content>
                    <div class="sm:hidden mb-3">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <InputText v-model="q" placeholder="Buscar…" class="pl-9 w-full" />
                        </div>
                    </div>

                    <DataTable :value="filtered" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]" dataKey="id"
                        responsiveLayout="scroll" class="text-sm">
                        <Column field="id" header="N°" style="width: 90px" sortable />
                        <Column field="cliente" header="Cliente" sortable />
                        <Column header="Tipo" sortable>
                            <template #body="{ data }">
                                <Tag :value="data.tipo || '—'" class="bg-indigo-50 text-indigo-700 border-indigo-200" />
                            </template>
                        </Column>
                        <Column header="Garantía" sortable>
                            <template #body="{ data }">
                                <Tag :value="data.garantia || '—'"
                                    class="bg-emerald-50 text-emerald-700 border-emerald-200" />
                            </template>
                        </Column>
                        <Column field="monto" header="Monto" sortable style="width: 160px">
                            <template #body="{ data }">{{ money(data.monto) }}</template>
                        </Column>
                        <Column field="plazo" header="Plazo" sortable style="width: 120px">
                            <template #body="{ data }">{{ data.plazo }} meses</template>
                        </Column>
                        <Column field="fecha_concesion" header="Concesión" sortable style="width: 130px" />
                        <Column header="Vencimiento" sortable style="width: 180px">
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <span>{{ data.fecha_vencimiento }}</span>
                                    <Tag :value="vencTag(data.fecha_vencimiento).label"
                                        :class="vencTag(data.fecha_vencimiento).cls" />
                                </div>
                            </template>
                        </Column>
                        <Column field="asesor" header="Asignado a" sortable>
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <UserRound class="w-4 h-4 text-gray-400" />
                                    <span>{{ data.asesor || '—' }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Acciones" style="width: 140px">
                            <template #body="{ data }">
                                <div class="flex gap-2">
                                    <Link v-if="canEditRow(data)" :href="route('creditos.edit', data.id)">
                                    <Button size="small" outlined>
                                        <Edit class="w-4 h-4 mr-2" /> Editar
                                    </Button>
                                    </Link>
                                    <Link v-else :href="route('creditos.show', data.id)">
                                    <Button size="small" outlined>
                                        <Eye class="w-4 h-4 mr-2" /> Visualizar
                                    </Button>
                                    </Link>
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                    <div v-if="filtered.length === 0" class="py-6 text-center text-gray-500">
                        No se encontraron créditos con ese filtro.
                    </div>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
