<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Calendar from 'primevue/calendar'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Divider from 'primevue/divider'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import { CalendarClock, Users, MapPin, Plus, X, Download } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  clientes: { type: Array, default: () => [] },
  asesores: { type: Array, default: () => [] },
  isAdmin:  { type: Boolean, default: false }
})

const toast = useToast()
const loading = ref(false)
const events  = ref([])

const fetchEvents = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(route('calendario.events'))
    events.value = data.events || []
  } finally {
    loading.value = false
  }
}
onMounted(fetchEvents)

const fmt = (d) => new Date(d).toLocaleString('es-GT', { dateStyle: 'medium', timeStyle: 'short' })
const groupByDay = computed(() => {
  const map = {}
  for (const ev of events.value) {
    const day = new Date(ev.start)
    const key = `${day.getFullYear()}-${String(day.getMonth()+1).padStart(2,'0')}-${String(day.getDate()).padStart(2,'0')}`
    map[key] ??= []
    map[key].push(ev)
  }
  return Object.entries(map)
    .sort(([a],[b]) => a.localeCompare(b))
    .map(([day, items]) => ({ day, items: items.sort((x,y) => x.start.localeCompare(y.start)) }))
})

// ===== Crear reunión =====
const dialog = ref(false)
const form = ref({
  cliente_id: null,
  title: '',
  description: '',
  start_at: null,
  end_at: null,
  location: ''
})
const saving = ref(false)
const createMeeting = async () => {
  if (!form.value.title || !form.value.start_at) {
    toast.add({ severity: 'warn', summary: 'Campos requeridos', detail: 'Título y fecha/hora de inicio.', life: 2200 })
    return
  }
  try {
    saving.value = true
    await axios.post(route('calendario.events.store'), {
      ...form.value,
      start_at: form.value.start_at ? new Date(form.value.start_at).toISOString() : null,
      end_at:   form.value.end_at   ? new Date(form.value.end_at).toISOString()   : null
    })
    toast.add({ severity: 'success', summary: 'Reunión creada', life: 1800 })
    dialog.value = false
    form.value = { cliente_id: null, title:'', description:'', start_at:null, end_at:null, location:'' }
    await fetchEvents()
  } catch (e) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo crear la reunión.', life: 2400 })
  } finally {
    saving.value = false
  }
}

const deleteMeeting = async (ev) => {
  if (!String(ev.id).startsWith('M')) return
  const id = String(ev.id).replace(/^M/, '')
  await axios.delete(route('calendario.events.destroy', id))
  await fetchEvents()
  toast.add({ severity: 'success', summary: 'Evento eliminado', life: 1500 })
}

// ===== Filtros para exportar reporte =====
const filters = ref({
  cliente_id: null,
  asesor_id:  null,
  from: null,
  to: null,
  include: ['meeting','credito_vencimiento','cuenta_actualizacion']
})
const toISODate = (d) => {
  if (!d) return null
  const yy = d.getFullYear()
  const mm = String(d.getMonth()+1).padStart(2,'0')
  const dd = String(d.getDate()).padStart(2,'0')
  return `${yy}-${mm}-${dd}`
}
const exportCsv = () => {
  const p = new URLSearchParams()
  if (filters.value.cliente_id) p.append('cliente_id', filters.value.cliente_id)
  if (props.isAdmin && filters.value.asesor_id) p.append('asesor_id', filters.value.asesor_id)
  if (filters.value.from) p.append('from', toISODate(filters.value.from))
  if (filters.value.to)   p.append('to',   toISODate(filters.value.to))
  for (const t of filters.value.include) p.append('include[]', t)
  const url = route('calendario.report') + '?' + p.toString()
  window.open(url, '_blank')
}
</script>

<template>
  <Head title="Calendario" />
  <AuthenticatedLayout>
    <Toast />
    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
          <CalendarClock class="w-5 h-5" /> Calendario
        </h1>
        <div class="flex gap-2">
          <Button outlined @click="exportCsv">
            <Download class="w-4 h-4 mr-2" /> Exportar CSV
          </Button>
          <Button @click="dialog = true">
            <Plus class="w-4 h-4 mr-2" /> Nueva reunión
          </Button>
        </div>
      </div>

      <!-- Filtros para exportar -->
      <Card class="rounded-xl shadow-sm">
        <template #content>
          <div class="grid grid-cols-1 lg:grid-cols-6 gap-3 items-end">
            <div class="lg:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
              <Dropdown v-model="filters.cliente_id" :options="props.clientes" optionLabel="nombre_cliente" optionValue="id" placeholder="Todos" class="w-full" />
            </div>

            <div v-if="props.isAdmin" class="lg:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Asesor</label>
              <Dropdown v-model="filters.asesor_id" :options="props.asesores" optionLabel="name" optionValue="id" placeholder="Todos" class="w-full" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
              <Calendar v-model="filters.from" showIcon dateFormat="yy-mm-dd" class="w-full"/>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
              <Calendar v-model="filters.to" showIcon dateFormat="yy-mm-dd" class="w-full"/>
            </div>
          </div>
        </template>
      </Card>

      <!-- Lista por día -->
      <Card class="rounded-xl shadow-sm">
        <template #content>
          <div v-if="loading" class="text-gray-500">Cargando…</div>
          <div v-else class="space-y-6">
            <div v-for="grp in groupByDay" :key="grp.day" class="space-y-2">
              <div class="text-sm font-semibold text-gray-600">{{ new Date(grp.day).toLocaleDateString('es-GT', { dateStyle: 'full' }) }}</div>
              <div class="space-y-2">
                <div v-for="ev in grp.items" :key="ev.id" class="rounded-xl border border-gray-200 p-3 flex items-start justify-between">
                  <div>
                    <div class="font-medium text-gray-800">
                      {{ ev.title }}
                      <span v-if="ev.type === 'credito_vencimiento'" class="ml-2 inline-flex text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Vencimiento crédito</span>
                      <span v-else-if="ev.type === 'cuenta_actualizacion'" class="ml-2 inline-flex text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">Actualización cuenta</span>
                    </div>
                    <div class="text-sm text-gray-600">{{ fmt(ev.start) }}</div>
                    <div v-if="ev.cliente" class="text-sm text-gray-700 mt-1 flex items-center gap-1">
                      <Users class="w-4 h-4" /> {{ ev.cliente.nombre }}
                    </div>
                    <div v-if="ev.location" class="text-sm text-gray-700 mt-1 flex items-center gap-1">
                      <MapPin class="w-4 h-4" /> {{ ev.location }}
                    </div>
                  </div>
                  <div v-if="String(ev.id).startsWith('M')">
                    <Button size="small" outlined severity="danger" @click="deleteMeeting(ev)">
                      <X class="w-4 h-4 mr-1" /> Eliminar
                    </Button>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="groupByDay.length === 0" class="text-gray-500">No hay eventos programados.</div>
          </div>
        </template>
      </Card>

      <!-- Dialog crear reunión -->
      <Card v-if="dialog" class="rounded-xl shadow-lg">
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente (opcional)</label>
              <Dropdown v-model="form.cliente_id" :options="props.clientes" optionLabel="nombre_cliente" optionValue="id" placeholder="Selecciona cliente" class="w-full" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Lugar (opcional)</label>
              <InputText v-model="form.location" class="w-full" placeholder="Sala de reuniones / Meet" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
              <InputText v-model="form.title" class="w-full" placeholder="Reunión de seguimiento" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
              <Textarea v-model="form.description" class="w-full" rows="3" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Inicio</label>
              <Calendar v-model="form.start_at" showTime hourFormat="24" class="w-full" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fin (opcional)</label>
              <Calendar v-model="form.end_at" showTime hourFormat="24" class="w-full" />
            </div>
          </div>
          <Divider />
          <div class="flex justify-end gap-2">
            <Button outlined @click="dialog=false">Cancelar</Button>
            <Button :loading="saving" @click="createMeeting">Guardar</Button>
          </div>
        </template>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
