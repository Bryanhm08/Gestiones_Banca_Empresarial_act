<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineOptions({ layout: AuthenticatedLayout })

import { Head, Link, useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'

const props = defineProps({ lib: Object })

// ID generator sin dependencias (con fallback)
const newId = () =>
  (typeof crypto !== 'undefined' && crypto.randomUUID)
    ? crypto.randomUUID()
    : (Math.random().toString(36).slice(2) + Date.now().toString(36))

// Clonamos para no mutar props
const form = useForm({
  columns: JSON.parse(JSON.stringify(props.lib.columns)),
  rows:    JSON.parse(JSON.stringify(props.lib.rows || [])),
})

const addCol = () => {
  const label = prompt('Nombre de la nueva columna:')
  if (!label) return
  form.columns.push({
    id: (label.toLowerCase().replace(/\s+/g, '_')) + '_' + Math.random().toString(36).slice(2, 4),
    label
  })
}

const addRow = () => {
  form.rows.push({ id: newId(), status:'pendiente', values:{} })
}

const removeCol = (colId) => {
  if (!confirm('Se eliminará la columna y sus datos. ¿Continuar?')) return
  form.columns = form.columns.filter(c => c.id !== colId)
  form.rows.forEach(r => { if (r.values[colId] !== undefined) delete r.values[colId] })
}

const removeRow = (rowId) => {
  if (!confirm('Se eliminará la fila y sus datos. ¿Continuar?')) return
  form.rows = form.rows.filter(r => r.id !== rowId)
}

const save = () => form.patch(route('liberaciones.update', props.lib.id))
</script>

<template>
  <Head :title="`Editar liberación #${props.lib.id}`" />
  <div class="p-4 space-y-4">
    <div class="flex items-center gap-2">
      <Link :href="route('liberaciones.index')">
        <Button label="Volver" icon="pi pi-arrow-left" text />
      </Link>
      <h1 class="text-2xl font-semibold">Editar estructura</h1>
    </div>

    <div class="flex gap-2">
      <Button label="Crear fila" icon="pi pi-plus" text @click="addRow" />
      <Button label="Crear columna" icon="pi pi-plus-circle" text @click="addCol" />
    </div>

    <div class="overflow-auto rounded-2xl border">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 sticky top-0">
          <tr>
            <th v-for="c in form.columns" :key="c.id" class="px-3 py-2 text-left font-semibold">
              <div class="flex items-center gap-2">
                <span>{{ c.label }}</span>
                <button class="w-6 h-6 rounded-full bg-rose-100" title="Eliminar columna" @click="removeCol(c.id)">−</button>
              </div>
            </th>
            <th class="px-3 py-2 font-semibold">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in form.rows" :key="r.id" class="border-t">
            <td v-for="c in form.columns" :key="c.id" class="px-3 py-2">
              <InputText v-model="r.values[c.id]" class="w-full" />
            </td>
            <td class="px-3 py-2">
              <button class="w-6 h-6 rounded-full bg-rose-100" title="Eliminar fila" @click="removeRow(r.id)">−</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex justify-end">
      <Button label="Guardar cambios" icon="pi pi-check" @click="save" />
    </div>
  </div>
</template>
