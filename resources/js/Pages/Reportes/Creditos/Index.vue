<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import MultiSelect from 'primevue/multiselect'
import Calendar from 'primevue/calendar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import { Filter, Download, Search, AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
    filters: Object,
    rows: Array,
    kpis: Object,
    catalogs: Object,
})

const toYMD = v => (v instanceof Date ? v.toISOString().slice(0, 10) : v || '')

const downloadBlob = (blob, filename = 'reporte.xlsx') => {
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
}

const filenameFromHeaders = (headers) => {
    const cd = headers?.['content-disposition'] || headers?.get?.('content-disposition') || ''
    const m = cd.match(/filename\*=UTF-8''([^;]+)|filename="?([^"]+)"?/i)
    return decodeURIComponent(m?.[1] || m?.[2] || 'reporte.xlsx')
}

const exporting = ref(false)

const f = ref({
    from: props.filters?.from || '',
    to: props.filters?.to || '',
    q: props.filters?.q || '',
    asesor: props.filters?.asesor || null,
    tipos: props.filters?.tipos || [],
    estados: props.filters?.estados || [],
})

const exportXlsx = async () => {
    try {
        exporting.value = true
        const params = {
            ...f.value,
            from: toYMD(f.value.from),
            to: toYMD(f.value.to),
            export: 1,
        }
        const url = route('reportes.creditos', params)
        const res = await axios.get(url, {
            responseType: 'blob',
            headers: {
                Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            },
        })
        const filename = filenameFromHeaders(res.headers) || 'reporte_creditos.xlsx'
        const blob = new Blob([res.data], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        })
        downloadBlob(blob, filename)
    } catch (e) {
        // Si usás Toast, acá podrías inyectarlo; lo dejo como console.error igual que antes.
        console.error('No se pudo exportar', e)
    } finally {
        exporting.value = false
    }
}

const money = (n) =>
    new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(Number(n || 0))

const apply = () => {
    router.get(route('reportes.creditos'), f.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Si quisieras exportar vía navegación (CSV, etc.), queda este helper
const exportCsv = () => {
    router.get(
        route('reportes.creditos'),
        { ...f.value, export: 1 },
        { preserveState: true, preserveScroll: true },
    )
}
</script>

<template>
    <Head title="Reportes de créditos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Reportes de créditos</h2>
                <div class="flex gap-2">
                    <Button outlined @click="apply">
                        <Filter class="w-4 h-4 mr-2" /> Aplicar filtros
                    </Button>
                    <Button
                        :loading="exporting"
                        class="bg-blue-600 hover:bg-blue-700 text-white"
                        @click="exportXlsx"
                    >
                        <Download class="w-4 h-4 mr-2" /> Exportar Excel
                    </Button>
                </div>
            </div>
        </template>

        <!-- Filtros -->
        <Card class="rounded-2xl shadow-sm mb-4">
            <template #content>
                <div class="grid grid-cols-4 lg:grid-cols-4 gap-3">
                    <div class="lg:col-span-4">
                        <label class="text-xs text-gray-500">Buscar</label>
                        <div class="relative">
                            <Search
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                            />
                            <InputText
                                v-model="f.q"
                                placeholder="Cliente / NIT…"
                                class="pl-9 w-full"
                            />
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <label class="text-xs text-gray-500">Desde</label>
                        <Calendar v-model="f.from" dateFormat="yy-mm-dd" class="w-full" />
                    </div>

                    <div class="lg:col-span-1">
                        <label class="text-xs text-gray-500">Hasta</label>
                        <Calendar v-model="f.to" dateFormat="yy-mm-dd" class="w-full" />
                    </div>

                    <div class="lg:col-span-1">
                        <label class="text-xs text-gray-500">Asesor</label>
                        <Dropdown
                            v-model="f.asesor"
                            :options="catalogs.asesores"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            class="w-full"
                        />
                    </div>

                    <div class="lg:col-span-1">
                        <label class="text-xs text-gray-500">Tipo de crédito</label>
                        <MultiSelect
                            v-model="f.tipos"
                            :options="catalogs.tipos"
                            optionLabel="nombre"
                            optionValue="id"
                            placeholder="Todos"
                            display="chip"
                            class="w-full"
                        />
                    </div>

                    <div class="lg:col-span-4">
                        <label class="text-xs text-gray-500">Estado</label>
                        <MultiSelect
                            v-model="f.estados"
                            :options="catalogs.estados"
                            optionLabel="nombre"
                            optionValue="id"
                            placeholder="Todos"
                            display="chip"
                            class="w-full"
                        />
                    </div>
                </div>
            </template>
        </Card>

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
            <Card class="rounded-xl shadow-sm">
                <template #content>
                    <div class="text-xs text-gray-500">Créditos</div>
                    <div class="text-2xl font-semibold text-gray-800">
                        {{ kpis.total }}
                    </div>
                </template>
            </Card>

            <Card class="rounded-xl shadow-sm">
                <template #content>
                    <div class="text-xs text-gray-500">Monto total</div>
                    <div class="text-2xl font-semibold text-gray-800">
                        {{ money(kpis.montoTotal) }}
                    </div>
                </template>
            </Card>

            <Card class="rounded-xl shadow-sm">
                <template #content>
                    <div class="text-xs text-gray-500">Promedio</div>
                    <div class="text-2xl font-semibold text-gray-800">
                        {{ money(kpis.promedio) }}
                    </div>
                </template>
            </Card>

            <Card class="rounded-xl shadow-sm">
                <template #content>
                    <div class="text-xs text-gray-500">Vencidos</div>
                    <div class="text-2xl font-semibold text-rose-600 flex items-center gap-2">
                        <AlertTriangle class="w-5 h-5" /> {{ kpis.vencidos }}
                    </div>
                </template>
            </Card>
        </div>

        <!-- Tabla -->
        <Card class="rounded-2xl shadow-sm">
            <template #content>
                <DataTable
                    :value="rows"
                    dataKey="id"
                    paginator
                    :rows="15"
                    :rowsPerPageOptions="[15, 30, 50]"
                    class="text-sm"
                >
                    <Column field="id" header="N°" style="width: 90px" sortable />
                    <Column field="cliente" header="Cliente" sortable />

                    <Column header="Tipo" sortable>
                        <template #body="{ data }">
                            <Tag
                                :value="data.tipo || '—'"
                                class="bg-indigo-50 text-indigo-700 border-indigo-200"
                            />
                        </template>
                    </Column>

                    <Column header="Garantía" sortable>
                        <template #body="{ data }">
                            <Tag
                                :value="data.garantia || '—'"
                                class="bg-emerald-50 text-emerald-700 border-emerald-200"
                            />
                        </template>
                    </Column>

                    <Column field="monto" header="Monto" style="width: 160px" sortable>
                        <template #body="{ data }">
                            {{ money(data.monto) }}
                        </template>
                    </Column>

                    <Column field="plazo" header="Plazo" style="width: 120px" sortable>
                        <template #body="{ data }">
                            {{ data.plazo }} meses
                        </template>
                    </Column>

                    <!-- 🔻 Ya sin concesión ni vencimiento -->
                    <Column field="estado" header="Estado actual" />
                    <Column field="asesor" header="Asesor" sortable />
                </DataTable>
            </template>
        </Card>
    </AuthenticatedLayout>
</template>
