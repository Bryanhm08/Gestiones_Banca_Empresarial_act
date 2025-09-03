<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import { CreditCard, RefreshCw } from 'lucide-vue-next'
const recent = ref([])
const loadingRecent = ref(false)
const globalFilter = ref('')
const toast = useToast()
const apiRecentUrl = { type: String, default: '/cuentas/recent' };
async function fetchRecent() {
    loadingRecent.value = true
    try {
        const { data } = await axios.get(apiRecentUrl.default)
        recent.value = Array.isArray(data) ? data : []
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'No se pudo cargar la tabla',
            detail: 'Intenta recargar nuevamente.',
            life: 3500
        })
    } finally {
        loadingRecent.value = false
    }
}

function formatDate(d) {
    if (!d) return '—'
    const date = new Date(d)
    if (Number.isNaN(date.getTime())) return d
    return date.toLocaleDateString()
}

onMounted(async () => {
    await fetchRecent()
})
</script>
<template>
    <Card class="shadow-2 rounded-2xl">
        <template #title>
            <div class="flex items-center justify-between w-full gap-3">
                <div class="flex items-center gap-2">
                    <CreditCard class="w-5 h-5" />
                    <span>Cuentas creadas recientemente</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="hidden md:inline text-sm text-surface-500">Buscar</span>
                    <span class="p-input-icon-left">
                        <i class="pi pi-search" />
                        <InputText v-model="globalFilter" placeholder="Filtro global" class="w-48 md:w-64" />
                    </span>
                    <Button class="button-full" outlined @click="fetchRecent" :disabled="loadingRecent">
                        <template #icon>
                            <RefreshCw class="w-4 h-4" />
                        </template>
                        <span class="hidden sm:inline">Recargar</span>
                    </Button>
                </div>
            </div>
        </template>

        <template #content>
            <div v-if="loadingRecent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <Skeleton v-for="n in 6" :key="n" height="3.5rem" class="rounded-lg" />
            </div>

            <DataTable v-else :value="recent" paginator :rows="10" :rowsPerPageOptions="[5, 10, 20]"
                responsiveLayout="scroll"
                :globalFilterFields="['id', 'cliente', 'tipo_cuenta', 'asesor', 'fecha_apertura', 'created_at']"
                :filters="{ global: { value: globalFilter, matchMode: 'contains' } }"
                emptyMessage="No hay cuentas registradas recientemente." class="mt-2">
                <Column field="id" header="ID" sortable style="width: 90px" />
                <Column field="cliente" header="Cliente" sortable />
                <Column field="tipo_cuenta" header="Tipo" sortable />
                <Column field="asesor" header="Asesor" sortable />
                <Column header="Fecha de apertura" sortable :sortField="'fecha_apertura'">
                    <template #body="{ data }">{{ formatDate(data.fecha_apertura) }}</template>
                </Column>
                <Column header="Creada" :sortField="'created_at'" sortable>
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>
            </DataTable>
        </template>
    </Card>
</template>
<style scoped>
.button-full {
    width: 100% !important;
}
</style>
