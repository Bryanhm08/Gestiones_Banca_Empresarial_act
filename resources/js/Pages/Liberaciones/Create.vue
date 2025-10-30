<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineOptions({ layout: AuthenticatedLayout })

import { Head, Link, useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'

// Generador de IDs seguro (sin librerías externas)
const newId = () =>
  (typeof crypto !== 'undefined' && crypto.randomUUID)
    ? crypto.randomUUID()
    : (Math.random().toString(36).slice(2) + Date.now().toString(36))

const props = defineProps({ defaultCols: Array, recentClients: Array })

const form = useForm({
  cliente_id: null,
  nombre: '',
  columns: props.defaultCols,
  rows: []
})

const addRow = () => {
  form.rows.push({ id: newId(), status: 'pendiente', values: {} })
}

const addColumn = () => {
  const label = prompt('Nombre de la nueva columna:')
  if (!label) return
  const id = (label.toLowerCase().replace(/\s+/g, '_')) + '_' + Math.random().toString(36).slice(2, 5)
  form.columns.push({ id, label })
}

const save = () => form.post(route('liberaciones.store'))
</script>

<template>
  <Head title="Nueva Liberación" />
  <div class="p-4 space-y-4">
    <div class="flex items-center gap-2">
      <Link :href="route('liberaciones.index')">
        <Button label="Volver" icon="pi pi-arrow-left" text />
      </Link>
      <h1 class="text-2xl font-semibold">Agregar listado de liberaciones</h1>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
      <div class="col-span-2 space-y-3">
        <div class="flex gap-2 items-end">
          <div class="flex-1">
            <label class="text-sm font-medium">Cliente (opcional)</label>
            <Dropdown
              v-model="form.cliente_id"
              :options="recentClients"
              optionValue="id"
              optionLabel="nombre"
              filter
              showClear
              placeholder="Buscar..."
              class="w-full"
            />
          </div>
          <div class="flex-1">
            <label class="text-sm font-medium">Nombre del cuadro</label>
            <InputText v-model="form.nombre" class="w-full" placeholder="Ej. Liberaciones Q4 2025" />
          </div>
        </div>

        <div class="rounded-2xl border">
          <div class="flex items-center justify-between p-3">
            <div class="font-medium">Tabla de amortizaciones</div>
            <div class="flex gap-2">
              <Button label="Crear fila" icon="pi pi-plus" @click="addRow" text />
              <Button label="Crear columna" icon="pi pi-plus-circle" @click="addColumn" text />
            </div>
          </div>

          <div class="overflow-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-50 sticky top-0">
                <tr>
                  <th
                    v-for="c in form.columns"
                    :key="c.id"
                    class="px-3 py-2 text-left font-semibold"
                  >
                    {{ c.label }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in form.rows" :key="r.id" class="border-t">
                  <td v-for="c in form.columns" :key="c.id" class="px-3 py-2">
                    <InputText v-model="r.values[c.id]" class="w-full" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-3 flex justify-end">
            <Button label="Crear tabla de amortizaciones" icon="pi pi-check" @click="save" />
          </div>
        </div>
      </div>

      <div class="space-y-2">
        <div class="text-sm font-medium">Clientes creados recientemente</div>
        <ul class="space-y-1 text-sm">
          <li v-for="c in recentClients" :key="c.id" class="flex justify-between">
            <span>{{ c.nombre }}</span>
            <Button icon="pi pi-plus" text @click="form.cliente_id = c.id" />
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
