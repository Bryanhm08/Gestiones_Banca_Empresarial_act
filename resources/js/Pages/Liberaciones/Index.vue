<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineOptions({ layout: AuthenticatedLayout })

import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

const props = defineProps({ libs:Object, ultimosClientes:Array })
const q = ref(new URLSearchParams(location.search).get('q') || '')

const search = () => {
  const url = new URL(route('liberaciones.index'), location.origin)
  if (q.value) url.searchParams.set('q', q.value); else url.searchParams.delete('q')
  location.href = url.toString()
}
</script>

<template>
  <Head title="Liberaciones" />
  <div class="p-4 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Liberaciones</h1>
      <div class="flex gap-2">
        <span class="p-input-icon-left">
          <i class="pi pi-search" />
          <InputText v-model="q" placeholder="Filtro global" @keydown.enter="search" />
        </span>
        <Button label="Recargar" icon="pi pi-refresh" @click="search"/>
        <Link :href="route('liberaciones.create')"><Button label="Agregar listado de liberaciones" icon="pi pi-plus" /></Link>
      </div>
    </div>

    <DataTable :value="libs.data" dataKey="id" responsiveLayout="scroll" class="shadow rounded-xl">
      <Column field="id" header="ID" sortable />
      <Column header="Cliente">
        <!-- usamos 'cliente_nombre' calculado en el controlador -->
        <template #body="{ data }">{{ data.cliente_nombre ?? '—' }}</template>
      </Column>
      <Column field="nombre" header="Nombre" />
      <Column field="created_at" header="Creada" />
      <Column header="">
        <template #body="{ data }">
          <div class="flex gap-2">
            <Link :href="route('liberaciones.show', data.id)"><Button label="Administrar" text /></Link>
            <Link :href="route('liberaciones.edit', data.id)"><Button label="Editar" text /></Link>
          </div>
        </template>
      </Column>
    </DataTable>

    <div class="text-sm">
      <div class="font-medium mb-1">Clientes creados recientemente</div>
      <div class="flex flex-wrap gap-2">
        <span v-for="c in ultimosClientes" :key="c.id" class="px-2 py-1 rounded-full bg-slate-100">{{ c.nombre }}</span>
      </div>
    </div>
  </div>
</template>
