<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import Calendar from 'primevue/calendar'
import Dropdown from 'primevue/dropdown'
import Divider from 'primevue/divider'
import Tag from 'primevue/tag'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Timeline from 'primevue/timeline'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import {
  ArrowLeft, Plus, X, CalendarDays, BadgeDollarSign, Shield, User, Layers,
  Circle, FilePlus2, Search, Users, FileCheck, Wallet, Banknote, XCircle
} from 'lucide-vue-next'

const props = defineProps({
  credito: { type: Object, required: true },
  tipos: { type: Array, default: () => [] },
  garantias: { type: Array, default: () => [] },
  estadosCatalog: { type: Array, default: () => [] },
  timeline: { type: Array, default: () => [] },
  amortizaciones: { type: Array, default: () => [] },
  isAdmin: { type: Boolean, default: false },
})

const toast = useToast()
const confirm = useConfirm()

const form = ref({
  tipo_credito_id: props.credito.tipo_credito_id ?? null,
  garantia_id: props.credito.garantia_id ?? null,
  monto: props.credito.monto,
  plazo: props.credito.plazo,
  fecha_concesion: props.credito.fecha_concesion ? new Date(props.credito.fecha_concesion) : null,
})

const formatDate = (d) => {
  if (!d) return null
  const y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'), day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

const resumenVencimiento = computed(() => {
  const d = form.value.fecha_concesion
  const p = Number(form.value.plazo || 0)
  if (!d || !p) return props.credito.fecha_vencimiento || '—'
  const copy = new Date(d.getTime())
  copy.setMonth(copy.getMonth() + p)
  if (copy.getDate() !== d.getDate()) copy.setDate(0)
  return formatDate(copy)
})

const money = (n) => new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(Number(n || 0))

// ————— Timeline / Etapas —————
const estados = ref([...props.timeline])
const nuevoEstadoId = ref(null)
const addingStage = ref(false)

const addEstado = async () => {
  if (!nuevoEstadoId.value) {
    toast.add({ severity: 'warn', summary: 'Etapa', detail: 'Seleccioná una etapa.', life: 2000 })
    return
  }
  try {
    addingStage.value = true
    const { data } = await axios.post(route('creditos.estado.add', props.credito.id), { estado_id: nuevoEstadoId.value })
    estados.value = [...estados.value, data]
    nuevoEstadoId.value = null
    toast.add({ severity: 'success', summary: 'Etapa agregada', detail: data.estado, life: 1800 })
    requestAnimationFrame(() => {
      const scroller = document.getElementById('timeline-scroller')
      if (scroller) scroller.scrollLeft = scroller.scrollWidth
    })
  } catch {
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo agregar la etapa.', life: 2400 })
  } finally {
    addingStage.value = false
  }
}

const stageMeta = (name = '') => {
  const n = (name || '').toLowerCase()
  if (n.includes('sin estado')) return { Icon: Circle, chip: 'bg-gray-50 text-gray-700 border-gray-200', dot: 'bg-gray-400' }
  if (n.includes('solicitud')) return { Icon: FilePlus2, chip: 'bg-indigo-50 text-indigo-700 border-indigo-200', dot: 'bg-indigo-500' }
  if (n.includes('análisis') || n.includes('analisis')) return { Icon: Search, chip: 'bg-sky-50 text-sky-700 border-sky-200', dot: 'bg-sky-500' }
  if (n.includes('comité') || n.includes('comite')) return { Icon: Users, chip: 'bg-violet-50 text-violet-700 border-violet-200', dot: 'bg-violet-500' }
  if (n.includes('formalización') || n.includes('formalizacion')) return { Icon: FileCheck, chip: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500' }
  if (n.includes('asignado desembolso')) return { Icon: Wallet, chip: 'bg-cyan-50 text-cyan-700 border-cyan-200', dot: 'bg-cyan-500' }
  if (n.includes('desembolsado')) return { Icon: Banknote, chip: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-600' }
  if (n.includes('desistido')) return { Icon: XCircle, chip: 'bg-rose-50 text-rose-700 border-rose-200', dot: 'bg-rose-500' }
  return { Icon: Circle, chip: 'bg-gray-50 text-gray-700 border-gray-200', dot: 'bg-gray-400' }
}

// ————— Amortizaciones —————
const amortis = ref([...props.amortizaciones])
const nuevaFecha = ref(null)
const addingAmorti = ref(false)

const addAmortizacion = async () => {
  if (!nuevaFecha.value) {
    toast.add({ severity: 'warn', summary: 'Amortización', detail: 'Seleccioná una fecha.', life: 2000 })
    return
  }
  try {
    addingAmorti.value = true
    const { data } = await axios.post(route('creditos.amortizaciones.store', props.credito.id), {
      fecha_pago: formatDate(nuevaFecha.value),
      status: 'Pendiente',
    })
    amortis.value = [...amortis.value, data]
    nuevaFecha.value = null
    toast.add({ severity: 'success', summary: 'Añadida', detail: 'Amortización creada.', life: 1800 })
  } catch {
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo crear.', life: 2400 })
  } finally {
    addingAmorti.value = false
  }
}

const toggleAmortizacion = async (row) => {
  try {
    const { data } = await axios.patch(route('amortizaciones.toggle', row.id))
    row.status = data.status
    toast.add({ severity: 'success', summary: 'Actualizado', detail: `Estado: ${row.status}`, life: 1600 })
  } catch {
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo actualizar.', life: 2400 })
  }
}

const deleteAmortizacion = (row) => {
  confirm.require({
    message: '¿Eliminar esta amortización?',
    header: 'Confirmar',
    acceptLabel: 'Sí, eliminar',
    rejectLabel: 'Cancelar',
    accept: async () => {
      try {
        await axios.delete(route('amortizaciones.destroy', row.id))
        amortis.value = amortis.value.filter(a => a.id !== row.id)
        toast.add({ severity: 'success', summary: 'Eliminada', detail: 'Amortización eliminada.', life: 1600 })
      } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo eliminar.', life: 2400 })
      }
    },
  })
}
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <Toast />
    <ConfirmDialog />

    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-xl font-semibold tracking-tight flex items-center gap-2">
          <Layers class="w-5 h-5 text-gray-700" />
          Editar crédito #{{ credito.id }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Cliente: <span class="font-medium text-gray-700">{{ credito.cliente }}</span> ·
          Asesor: <span class="font-medium text-gray-700">{{ credito.asesor }}</span>
        </p>
      </div>
      <div class="flex gap-2">
        <Button outlined @click="router.visit(route('creditos.index'))">
          <ArrowLeft class="w-4 h-4 mr-2" /> Volver
        </Button>
        <!-- Botón Guardar eliminado a solicitud -->
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <Card class="rounded-xl shadow-sm"><template #content>
        <div class="text-xs text-gray-500">Monto</div>
        <div class="text-lg font-semibold">{{ money(form.monto) }}</div>
      </template></Card>
      <Card class="rounded-xl shadow-sm"><template #content>
        <div class="text-xs text-gray-500">Plazo</div>
        <div class="text-lg font-semibold">{{ form.plazo }} meses</div>
      </template></Card>
      <Card class="rounded-xl shadow-sm"><template #content>
        <div class="text-xs text-gray-500">Concesión</div>
        <div class="text-lg font-semibold">{{ formatDate(form.fecha_concesion) || '—' }}</div>
      </template></Card>
      <Card class="rounded-xl shadow-sm"><template #content>
        <div class="text-xs text-gray-500">Vencimiento</div>
        <div class="text-lg font-semibold">{{ resumenVencimiento }}</div>
      </template></Card>
    </div>

    <Card class="rounded-2xl shadow-sm">
      <template #title>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Layers class="w-4 h-4 text-gray-600" />
            <span>Etapas del crédito</span>
          </div>
          <div class="flex gap-2">
            <Dropdown v-model="nuevoEstadoId" :options="estadosCatalog" optionLabel="nombre"
              optionValue="id" placeholder="Seleccioná etapa" class="w-56" />
            <Button :loading="addingStage" @click="addEstado">
              <Plus class="w-4 h-4 mr-1" /> Agregar
            </Button>
          </div>
        </div>
      </template>
      <template #content>
        <div id="timeline-scroller" class="overflow-x-auto pb-2">
          <div class="min-w-max pr-2">
            <Timeline :value="estados" layout="horizontal" class="w-full">
              <template #marker="slotProps">
                <span class="flex items-center justify-center w-7 h-7 rounded-full ring-2 ring-white shadow"
                      :class="stageMeta(slotProps.item.estado).dot">
                  <component :is="stageMeta(slotProps.item.estado).Icon" class="w-4 h-4 text-white" />
                </span>
              </template>
              <template #opposite="slotProps">
                <div class="text-[11px] text-gray-500 font-medium">
                  {{ slotProps.item.created_at || '—' }}
                </div>
              </template>
              <template #content="slotProps">
                <div class="inline-flex items-center gap-2 border rounded-full px-3 py-1 text-sm"
                     :class="stageMeta(slotProps.item.estado).chip">
                  <component :is="stageMeta(slotProps.item.estado).Icon" class="w-4 h-4" />
                  <span class="whitespace-nowrap">{{ slotProps.item.estado }}</span>
                </div>
              </template>
            </Timeline>
          </div>
        </div>

        <div v-if="estados.length === 0" class="text-sm text-gray-500 mt-2">Aún no hay etapas.</div>
      </template>
    </Card>

    <!-- Grid a 2 columnas para que Amortizaciones sea más ancha -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <Card class="rounded-2xl shadow-sm lg:col-span-1">
        <template #title>
          <div class="flex items-center gap-2">
            <User class="w-4 h-4 text-gray-600" />
            <span>Datos del crédito</span>
          </div>
        </template>
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="text-sm text-gray-700 flex items-center gap-2 mb-1">
                <Shield class="w-4 h-4" /> Tipo de crédito
              </label>
              <Dropdown v-model="form.tipo_credito_id" :options="tipos" optionLabel="nombre"
                        optionValue="id" placeholder="Seleccioná el tipo" class="w-full" filter />
            </div>

            <div>
              <label class="text-sm text-gray-700 flex items-center gap-2 mb-1">
                <Shield class="w-4 h-4" /> Garantía
              </label>
              <Dropdown v-model="form.garantia_id" :options="garantias" optionLabel="nombre"
                        optionValue="id" placeholder="Seleccioná la garantía" class="w-full" filter />
            </div>

            <div>
              <label class="text-sm text-gray-700 flex items-center gap-2 mb-1">
                <BadgeDollarSign class="w-4 h-4" /> Monto
              </label>
              <InputNumber v-model="form.monto" mode="currency" currency="GTQ" locale="es-GT" class="w-full" />
            </div>

            <div>
              <label class="text-sm text-gray-700 flex items-center gap-2 mb-1">
                <CalendarDays class="w-4 h-4" /> Plazo (meses)
              </label>
              <InputNumber v-model="form.plazo" :min="1" :max="360" showButtons class="w-full" />
            </div>

            <div>
              <label class="text-sm text-gray-700 flex items-center gap-2 mb-1">
                <CalendarDays class="w-4 h-4" /> Fecha de concesión
              </label>
              <Calendar v-model="form.fecha_concesion" dateFormat="yy-mm-dd" :showIcon="true" showButtonBar class="w-full" />
            </div>
          </div>
        </template>
      </Card>

      <!-- Amortizaciones con más ancho (50% en desktop) -->
      <Card class="rounded-2xl shadow-sm lg:col-span-1">
        <template #title>
          <div class="flex items-center gap-2">
            <CalendarDays class="w-4 h-4 text-gray-600" />
            <span>Amortizaciones</span>
          </div>
        </template>
        <template #content>
          <div class="flex items-center gap-3 mb-3">
            <Calendar v-model="nuevaFecha" dateFormat="yy-mm-dd" :showIcon="true" class="w-52" />
            <Button :loading="addingAmorti" @click="addAmortizacion">
              <Plus class="w-4 h-4 mr-1" /> Añadir
            </Button>
          </div>

          <DataTable :value="amortis" dataKey="id" paginator :rows="5" class="text-sm">
            <Column field="fecha_pago" header="Fecha" style="width: 140px" />
            <Column field="status" header="Estado" style="width: 120px">
              <template #body="{ data }">
                <Tag :value="data.status" :class="data.status === 'Pagado'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-amber-50 text-amber-700 border-amber-200'" />
              </template>
            </Column>
            <Column header="Acciones" style="width: 180px">
              <template #body="{ data }">
                <div class="flex gap-2">
                  <Button size="small" outlined @click="toggleAmortizacion(data)">Toggle</Button>
                  <Button size="small" outlined severity="danger" @click="deleteAmortizacion(data)">
                    <X class="w-4 h-4 mr-1" /> Eliminar
                  </Button>
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-card){ border-radius: .75rem }
:deep(.p-dropdown .p-inputtext){ font-size: .95rem }
:deep(.p-inputtext), :deep(.p-dropdown), :deep(.p-inputnumber), :deep(.p-calendar){ width:100% }
:deep(.p-timeline-event-connector){ background:#e5e7eb }
:deep(.p-timeline.p-timeline-horizontal .p-timeline-event-opposite){ margin-bottom:.25rem }
:deep(.p-timeline.p-timeline-horizontal .p-timeline-event-content){ margin-top:.25rem }
</style>
