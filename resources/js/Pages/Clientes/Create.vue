<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router, Head } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import axios from 'axios'

import Card from 'primevue/card'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Calendar from 'primevue/calendar'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import { User, Mail, Phone, IdCard, CalendarDays, ShieldCheck } from 'lucide-vue-next'

const props = defineProps({
  categorias: { type: Array, default: () => [] },
})

const toast = useToast()

const form = reactive({
  nombre_cliente: '',
  categoria_id: null,
  nit: '',
  telefono: '',
  email: '',
  fecha_nacimiento: null,
})

const required = (v) => v !== null && v !== undefined && String(v).trim?.() !== ''
const invalid = computed(() => ({
  nombre_cliente: !required(form.nombre_cliente),
  categoria_id:  !required(form.categoria_id),
  nit:           !required(form.nit),
  telefono:      !required(form.telefono),
  fecha_nacimiento: !form.fecha_nacimiento,
}))
const anyInvalid = computed(() => Object.values(invalid.value).some(Boolean))

const fmtDate = (d) => {
  if (!d) return null
  const yyyy = d.getFullYear()
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}`
}

const submit = async () => {
  if (anyInvalid.value) {
    toast.add({ severity: 'warn', summary: 'Faltan datos', detail: 'Revisá los campos resaltados.', life: 2500 })
    return
  }
  try {
    await axios.post(route('clientes.store'), {
      nombre_cliente: form.nombre_cliente,
      categoria_id: form.categoria_id,
      nit: form.nit,
      telefono: form.telefono,
      email: form.email || null,
      fecha_nacimiento: fmtDate(form.fecha_nacimiento),
      // 👇 NO enviamos asesor_id
    })
    toast.add({ severity: 'success', summary: '¡Listo!', detail: 'Cliente creado con éxito.', life: 2000 })
    setTimeout(() => router.visit(route('clientes.index')), 500)
  } catch (err) {
    if (err.response?.status === 422) {
      const msg = Object.values(err.response.data.errors || {}).flat()[0] || 'Revisá los campos.'
      toast.add({ severity: 'error', summary: 'Validación', detail: msg, life: 3000 })
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo guardar el cliente.', life: 3000 })
    }
  }
}
</script>

<template>
  <Head title="Nuevo Cliente" />
  <AuthenticatedLayout>
    <Toast />
    <div class="p-6 space-y-6 max-w-5xl mx-auto">
      <Card class="rounded-2xl shadow-sm">
        <template #title>
          <div class="flex items-center gap-2">
            <User class="w-5 h-5" />
            <span>Nuevo Cliente</span>
          </div>
        </template>

        <template #content>
          <p class="text-gray-600 mb-4">
            Completá los datos. Los campos con <strong>*</strong> son obligatorios.
          </p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-2">
              <label class="text-sm font-medium">Nombre del cliente *</label>
              <InputText v-model="form.nombre_cliente" class="w-full" :class="{'p-invalid': invalid.nombre_cliente}" />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <ShieldCheck class="w-4 h-4"/> Categoría *
              </label>
              <Dropdown
                v-model="form.categoria_id"
                :options="categorias"
                optionLabel="nombre"
                optionValue="id"
                placeholder="Seleccioná una categoría"
                class="w-full"
                :invalid="invalid.categoria_id"
                filter
              />
              <small v-if="invalid.categoria_id" class="text-red-500">Seleccioná una categoría.</small>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <IdCard class="w-4 h-4" /> NIT *
              </label>
              <InputText v-model="form.nit" class="w-full" :class="{'p-invalid': invalid.nit}" placeholder="EJ. 1234567-8" />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <CalendarDays class="w-4 h-4" /> Fecha de nacimiento / constitución *
              </label>
              <Calendar v-model="form.fecha_nacimiento" dateFormat="yy-mm-dd" :showIcon="true" showButtonBar class="w-full"
                        :invalid="invalid.fecha_nacimiento" />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <Phone class="w-4 h-4" /> Teléfono *
              </label>
              <InputText v-model="form.telefono" class="w-full" :class="{'p-invalid': invalid.telefono}" placeholder="0000-0000" />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <Mail class="w-4 h-4" /> Email
              </label>
              <InputText v-model="form.email" class="w-full" placeholder="correo@dominio.com" />
            </div>
          </div>

          <Divider class="my-6" />

          <div class="flex items-center justify-end gap-3">
            <Link :href="route('clientes.index')">
              <Button severity="secondary" outlined>Cancelar</Button>
            </Link>
            <Button @click="submit">Guardar</Button>
          </div>
        </template>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
:deep(.p-inputtext.p-invalid),
:deep(.p-dropdown.p-invalid .p-dropdown-label),
:deep(.p-calendar.p-invalid .p-inputtext) {
  border-color: #ef4444 !important;
}
</style>
