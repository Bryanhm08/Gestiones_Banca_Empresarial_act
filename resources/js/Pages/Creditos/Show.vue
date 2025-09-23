<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  credito: { type: Object, required: true },
})

/* ===== Helpers ===== */
const money = (n) =>
  new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' })
    .format(Number(n || 0))

const daysTo = (yyyyMMdd) => {
  if (!yyyyMMdd) return null
  const today = new Date(); today.setHours(0,0,0,0)
  const [y,m,d] = yyyyMMdd.split('-').map(Number)
  const target = new Date(y, m-1, d)
  return Math.round((target - today)/(1000*60*60*24))
}
const vencTag = computed(() => {
  const d = daysTo(props.credito?.fecha_vencimiento)
  if (d === null) return { label: '—', cls: 'bg-gray-100 text-gray-600 border-gray-200' }
  if (d < 0)     return { label: `Vencido ${Math.abs(d)}d`, cls: 'bg-red-50 text-red-700 border-red-200' }
  if (d <= 15)   return { label: `Vence en ${d}d`, cls: 'bg-amber-50 text-amber-700 border-amber-200' }
  return { label: `En ${d}d`, cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' }
})

/* ===== Form: Etapas ===== */
const etapa = ref({ nombre: '', estado: 'pendiente', comentario: '' })
const submitEtapa = () => {
  router.post(route('creditos.estado.add', props.credito.id), etapa.value, {
    onSuccess: () => etapa.value = { nombre: '', estado: 'pendiente', comentario: '' }
  })
}

/* ===== Form: Amortización ===== */
const amort = ref({ fecha: '', monto: null, descripcion: '' })
const submitAmort = () => {
  router.post(route('creditos.amortizaciones.store', props.credito.id), amort.value, {
    onSuccess: () => amort.value = { fecha: '', monto: null, descripcion: '' }
  })
}
const toggleAm = (id) => router.patch(`/amortizaciones/${id}/toggle`)
const delAm    = (id) => router.delete(`/amortizaciones/${id}`)

</script>

<template>
  <Head :title="`Crédito #${credito.id}`" />
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Crédito #{{ credito.id }}</h1>
        <div class="flex gap-2">
          <Link :href="route('creditos.index')" class="text-sm underline">← Volver</Link>
          <Link :href="route('creditos.edit', credito.id)"
                class="px-3 py-1 rounded-lg text-sm bg-blue-50 hover:bg-blue-100 ring-1 ring-blue-200">
            Editar
          </Link>
        </div>
      </div>

      <!-- Resumen -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl border p-4">
          <div class="text-xs text-gray-500">Cliente</div>
          <div class="font-medium">{{ credito?.cliente?.nombre ?? credito?.cliente_nombre ?? '—' }}</div>
        </div>
        <div class="rounded-2xl border p-4">
          <div class="text-xs text-gray-500">Monto</div>
          <div class="font-medium">{{ money(credito.monto) }}</div>
        </div>
        <div class="rounded-2xl border p-4">
          <div class="text-xs text-gray-500">Garantía</div>
          <div class="font-medium">{{ credito.garantia ?? '—' }}</div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-2xl border p-4">
          <div class="text-xs text-gray-500">Plazo</div>
          <div class="font-medium">{{ credito.plazo ?? '—' }} meses</div>
        </div>
        <div class="rounded-2xl border p-4">
          <div class="text-xs text-gray-500">Concesión</div>
          <div class="font-medium">{{ credito.fecha_concesion ?? '—' }}</div>
        </div>
        <div class="rounded-2xl border p-4">
          <div class="text-xs text-gray-500">Vencimiento</div>
          <div class="font-medium flex items-center gap-2">
            <span>{{ credito.fecha_vencimiento ?? '—' }}</span>
            <span :class="`px-2 py-0.5 rounded text-xs ring-1 ${vencTag.cls}`">
              {{ vencTag.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Etapas -->
      <div class="rounded-2xl border p-4 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Etapas</h2>
        </div>

        <div v-if="credito?.etapas?.length">
          <ul class="divide-y">
            <li v-for="et in credito.etapas" :key="et.id" class="py-3 flex items-center justify-between">
              <div>
                <div class="font-medium">{{ et.nombre }}</div>
                <div class="text-xs text-gray-500">
                  Estado: {{ et.estado }} <span v-if="et.fecha">· {{ et.fecha }}</span>
                </div>
                <div v-if="et.comentario" class="text-xs text-gray-500 mt-1">{{ et.comentario }}</div>
              </div>
              <!-- Aquí podrías enlazar a una edición puntual de etapa -->
            </li>
          </ul>
        </div>
        <div v-else class="text-sm text-gray-500">Sin etapas registradas.</div>

        <!-- Form nueva etapa -->
        <form @submit.prevent="submitEtapa" class="grid grid-cols-1 md:grid-cols-4 gap-2">
          <input v-model="etapa.nombre" required placeholder="Nombre de la etapa" class="p-2 border rounded" />
          <select v-model="etapa.estado" required class="p-2 border rounded">
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En proceso</option>
            <option value="completado">Completado</option>
          </select>
          <input v-model="etapa.comentario" placeholder="Comentario (opcional)" class="p-2 border rounded md:col-span-1" />
          <button type="submit" class="px-3 py-2 rounded bg-emerald-600 text-white">Agregar etapa</button>
        </form>
      </div>

      <!-- Amortizaciones -->
      <div class="rounded-2xl border p-4 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Amortizaciones</h2>
        </div>

        <div v-if="credito?.amortizaciones?.length">
          <ul class="divide-y">
            <li v-for="am in credito.amortizaciones" :key="am.id" class="py-3 flex items-center justify-between">
              <div>
                <div class="font-medium">
                  {{ am.fecha }} — {{ money(am.monto) }}
                  <span v-if="am.activo === 0" class="ml-2 text-xs px-2 py-0.5 rounded bg-gray-100 ring-1 ring-gray-200">Inactiva</span>
                </div>
                <div class="text-xs text-gray-500" v-if="am.descripcion">{{ am.descripcion }}</div>
              </div>
              <div class="flex gap-2">
                <button type="button" @click="toggleAm(am.id)" class="px-3 py-1 border rounded text-sm">Toggle</button>
                <button type="button" @click="delAm(am.id)" class="px-3 py-1 border rounded text-sm">Eliminar</button>
              </div>
            </li>
          </ul>
        </div>
        <div v-else class="text-sm text-gray-500">Sin amortizaciones.</div>

        <!-- Form nueva amortización -->
        <form @submit.prevent="submitAmort" class="grid grid-cols-1 md:grid-cols-4 gap-2">
          <input v-model="amort.fecha" type="date" required class="p-2 border rounded" />
          <input v-model.number="amort.monto" type="number" step="0.01" min="0" required placeholder="Monto" class="p-2 border rounded" />
          <input v-model="amort.descripcion" placeholder="Descripción (opcional)" class="p-2 border rounded" />
          <button type="submit" class="px-3 py-2 rounded bg-indigo-600 text-white">Registrar</button>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
