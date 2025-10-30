<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineOptions({ layout: AuthenticatedLayout })

import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'

const props = defineProps({ lib:Object, filters:Object })

const query = ref({
  correlativo: props.filters?.correlativo || '',
  cliente:     props.filters?.cliente || '',
  finca:       props.filters?.finca || '',
  folio:       props.filters?.folio || '',
  libro:       props.filters?.libro || '',
  monto:       props.filters?.monto || '',
  status:      props.filters?.status || ''
})

const filteredRows = computed(()=>{
  const rows = props.lib.rows || []
  return rows.filter(r=>{
    const v = (k)=> (r.values?.[k] ?? '').toString().toLowerCase()
    return (!query.value.correlativo || v('correlativo').includes(query.value.correlativo.toLowerCase()))
        && (!query.value.cliente     || v('cliente').includes(query.value.cliente.toLowerCase()))
        && (!query.value.finca       || v('finca').includes(query.value.finca.toLowerCase()))
        && (!query.value.folio       || v('folio').includes(query.value.folio.toLowerCase()))
        && (!query.value.libro       || v('libro').includes(query.value.libro.toLowerCase()))
        && (!query.value.monto       || v('monto').includes(query.value.monto.toLowerCase()))
        && (!query.value.status      || (r.status||'pendiente')===query.value.status)
  })
})

const setStatus = (rowId, newStatus) => {
  router.patch(route('liberaciones.rows.status',[props.lib.id,rowId]), {status:newStatus}, {preserveScroll:true})
}
</script>

<template>
  <Head :title="`Administrar liberación #${lib.id}`" />
  <div class="p-4 space-y-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <a :href="route('liberaciones.index')"><Button label="Volver" icon="pi pi-arrow-left" text/></a>
        <h1 class="text-2xl font-semibold">Administrar – Liberación #{{ lib.id }}</h1>
      </div>
      <div class="flex gap-2">
        <a :href="route('liberaciones.export', lib.id)"><Button label="Generar Excel" icon="pi pi-file-excel" /></a>
        <a :href="route('liberaciones.edit', lib.id)"><Button label="Editar" icon="pi pi-pencil" text/></a>
      </div>
    </div>

    <div class="grid md:grid-cols-6 gap-2">
      <InputText v-model="query.correlativo" placeholder="Correlativo"/>
      <InputText v-model="query.cliente" placeholder="Cliente"/>
      <InputText v-model="query.finca" placeholder="Finca"/>
      <InputText v-model="query.folio" placeholder="Folio"/>
      <InputText v-model="query.libro" placeholder="Libro"/>
      <InputText v-model="query.monto" placeholder="Monto"/>
    </div>

    <div class="overflow-auto rounded-2xl border">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 sticky top-0">
          <tr>
            <th v-for="c in lib.columns" :key="c.id" class="px-3 py-2 text-left font-semibold">{{ c.label }}</th>
            <th class="px-3 py-2 font-semibold">Estatus</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in filteredRows" :key="r.id" class="border-t">
            <td v-for="c in lib.columns" :key="c.id" class="px-3 py-2">
              {{ r.values?.[c.id] ?? '' }}
            </td>
            <td class="px-3 py-2">
              <div class="inline-flex rounded-full shadow-sm overflow-hidden">
                <button class="px-3 py-1 text-xs" :class="[(r.status||'pendiente')==='pendiente' ? 'bg-amber-100 font-medium':'bg-slate-100']" @click="setStatus(r.id,'pendiente')">Pendiente de liberación</button>
                <button class="px-3 py-1 text-xs" :class="[(r.status||'pendiente')==='liberado' ? 'bg-emerald-100 font-medium':'bg-slate-100']" @click="setStatus(r.id,'liberado')">Liberado</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>
