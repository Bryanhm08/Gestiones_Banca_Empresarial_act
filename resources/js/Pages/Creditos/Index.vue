<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  creditos:   { type: Object, default: null },  // paginator { data, links, meta... } o null
  canCreate:  { type: Boolean, default: true },
  isAdmin:    { type: Boolean, default: false },
  userId:     { type: Number,  default: null },
})

// Fallbacks seguros para no romper si algo llega null/undefined
const rows  = computed(() => {
  if (!props.creditos) return []
  // si en algún momento enviaron un array plano:
  if (Array.isArray(props.creditos)) return props.creditos
  return Array.isArray(props.creditos.data) ? props.creditos.data : []
})
const links = computed(() => Array.isArray(props.creditos?.links) ? props.creditos.links : [])

const fmtDate = (s) => {
  if (!s) return '—'
  try {
    const d = new Date(s)
    if (Number.isNaN(d.getTime())) return s
    const yyyy = d.getFullYear()
    const mm   = String(d.getMonth() + 1).padStart(2, '0')
    const dd   = String(d.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
  } catch { return s }
}
const fmtMoney = (n) => {
  const v = Number(n ?? 0)
  return new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(v)
}

const go = (url) => { if (url) router.visit(url) }
</script>

<template>
  <Head title="Créditos" />
  <AuthenticatedLayout>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold tracking-tight">Créditos</h1>

        <div v-if="canCreate">
          <Link :href="route('creditos.create')"
                class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
            Nuevo crédito
          </Link>
        </div>
      </div>

      <!-- Estado vacío -->
      <div v-if="rows.length === 0" class="rounded-xl border border-dashed p-10 text-center text-gray-500">
        No hay créditos registrados.
        <div v-if="canCreate" class="mt-4">
          <Link :href="route('creditos.create')" class="text-emerald-700 hover:underline">Crear el primero →</Link>
        </div>
      </div>

      <!-- Tabla -->
      <div v-else class="overflow-x-auto rounded-xl border">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Garantía</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plazo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concesión</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="c in rows" :key="c.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-700">{{ c.id }}</td>
              <td class="px-4 py-3">
                {{ c.cliente?.nombre_cliente ?? '—' }}
              </td>
              <td class="px-4 py-3">
                {{ c.tipo_credito?.nombre || c.tipoCredito?.nombre || '—' }}
              </td>
              <td class="px-4 py-3">
                {{ c.garantia?.nombre || '—' }}
              </td>
              <td class="px-4 py-3 font-medium">{{ fmtMoney(c.monto) }}</td>
              <td class="px-4 py-3">{{ c.plazo ?? '—' }} mes(es)</td>
              <td class="px-4 py-3">{{ fmtDate(c.fecha_concesion) }}</td>
              <td class="px-4 py-3">{{ fmtDate(c.fecha_vencimiento) }}</td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <Link :href="route('creditos.show', c.id)"
                        class="text-emerald-700 hover:underline">Ver</Link>
                  <span class="text-gray-300">|</span>
                  <Link :href="route('creditos.edit', c.id)"
                        class="text-gray-700 hover:underline">Editar</Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="links.length > 0" class="flex flex-wrap items-center gap-2 pt-2">
        <button v-for="(l, idx) in links" :key="idx"
                :disabled="!l.url"
                @click="go(l.url)"
                class="px-3 py-1.5 rounded border"
                :class="[
                  l.active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 hover:bg-gray-50',
                  !l.url && !l.active ? 'opacity-50 cursor-not-allowed' : ''
                ]"
                v-html="l.label">
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
