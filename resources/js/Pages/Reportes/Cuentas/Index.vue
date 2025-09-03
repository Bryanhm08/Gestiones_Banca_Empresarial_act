<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import { Filter, Download } from 'lucide-vue-next'

const props = defineProps({
    filters: Object,
    rows: Array,
    kpis: Object,
    catalogs: Object,
})
const toYMD = v => (v instanceof Date ? v.toISOString().slice(0, 10) : v || '')
const exporting = ref(false)
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
const f = ref({
    from: props.filters?.from || '',
    to: props.filters?.to || '',
    asesor: props.filters?.asesor || null,
    tipo: props.filters?.tipo || null,
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
        const url = route('reporteria.cuentas', params)

        const res = await axios.get(url, {
            responseType: 'blob',
            headers: { Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' },
        })

        const filename = filenameFromHeaders(res.headers) || 'reporteria_cuentas.xlsx'
        const blob = new Blob([res.data], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        })
        downloadBlob(blob, filename)
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo exportar' })
        console.error(e)
    } finally {
        exporting.value = false
    }
}
const apply = () => {
    router.get(route('reporteria.cuentas'), f.value, { preserveState: true, preserveScroll: true })
}
const exportCsv = () => {
    router.get(route('reporteria.cuentas'), { ...f.value, export: 1 }, { preserveState: true, preserveScroll: true })
}
</script>

<template>

    <Head title="Reportería de cuentas" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Reportería de cuentas</h2>
                <div class="flex gap-2">
                    <Button outlined @click="apply">
                        <Filter class="w-4 h-4 mr-2" /> Aplicar filtros
                    </Button>
                    <Button :loading="exporting" class="bg-blue-600 hover:bg-blue-700 text-white" @click="exportXlsx">
                        <Download class="w-4 h-4 mr-2" /> Exportar Excel
                    </Button>
                </div>
            </div>
        </template>
        <Card class="rounded-2xl shadow-sm mb-4">
            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        <label class="text-xs text-gray-500">Desde</label>
                        <Calendar v-model="f.from" dateFormat="yy-mm-dd" class="w-full" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Hasta</label>
                        <Calendar v-model="f.to" dateFormat="yy-mm-dd" class="w-full" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Asesor</label>
                        <Dropdown v-model="f.asesor" :options="catalogs.asesores" optionLabel="name" optionValue="id"
                            placeholder="Todos" class="w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Tipo de cuenta</label>
                        <Dropdown v-model="f.tipo" :options="catalogs.tipos" optionLabel="nombre" optionValue="id"
                            placeholder="Todos" class="w-full" />
                    </div>
                </div>
            </template>
        </Card>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <Card class="rounded-xl shadow-sm"><template #content>
                    <div class="text-xs text-gray-500">Cuentas</div>
                    <div class="text-2xl font-semibold text-gray-800">{{ kpis.total }}</div>
                </template></Card>
        </div>
        <Card class="rounded-2xl shadow-sm">
            <template #content>
                <DataTable :value="rows" dataKey="id" paginator :rows="15" :rowsPerPageOptions="[15, 30, 50]"
                    class="text-sm">
                    <Column field="id" header="N°" style="width: 90px" sortable />
                    <Column field="cliente" header="Cliente" sortable />
                    <Column header="Tipo" sortable>
                        <template #body="{ data }">
                            <Tag :value="data.tipo || '—'" class="bg-indigo-50 text-indigo-700 border-indigo-200" />
                        </template>
                    </Column>
                    <Column field="asesor" header="Asesor" sortable />
                    <Column field="fecha_apertura" header="Apertura" sortable style="width: 140px" />
                </DataTable>
            </template>
        </Card>
    </AuthenticatedLayout>
</template>
