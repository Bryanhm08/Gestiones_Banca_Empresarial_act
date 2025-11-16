<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router, Head } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import Card from 'primevue/card'
import Dropdown from 'primevue/dropdown'
import InputNumber from 'primevue/inputnumber'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import Tag from 'primevue/tag'
import Tooltip from 'primevue/tooltip'

import { BadgeDollarSign, Bookmark, Shield, UserCircle, Save, ArrowLeft, Sparkles, CalendarDays } from 'lucide-vue-next'

const props = defineProps({
  clientes: { type: Array, default: () => [] },
  tipos: { type: Array, default: () => [] },
  garantias: { type: Array, default: () => [] },
  asesores: { type: Array, default: () => [] }, // sólo para admin
  isAdmin: { type: Boolean, default: false },
  defaultAsesorId: { type: Number, default: null }, // id del usuario autenticado
})

const toast = useToast()

const form = ref({
  cliente_id: null,
  tipo_credito_id: null,
  garantia_id: null,
  monto: null,
  plazo: 12,
  // En el nuevo enfoque de pipeline, ya no se solicita fecha_concesion
  asesor_id: props.isAdmin ? null : props.defaultAsesorId,
})

const errors = ref({})
const loading = ref(false)

const required = (v) => v !== null && v !== undefined && String(v).trim?.() !== ''

const invalid = computed(() => ({
  cliente_id: !required(form.value.cliente_id),
  tipo_credito_id: !required(form.value.tipo_credito_id),
  garantia_id: !required(form.value.garantia_id),
  monto: !(form.value.monto > 0),
  plazo: !(Number(form.value.plazo) >= 1),
  asesor_id: props.isAdmin ? !required(form.value.asesor_id) : false,
}))
const anyInvalid = computed(() => Object.values(invalid.value).some(Boolean))

watch(form, () => { errors.value = {} }, { deep: true })

onMounted(() => {
  // Admin: si hay asesores y no se seleccionó, preselecciona el primero
  if (props.isAdmin && Array.isArray(props.asesores) && props.asesores.length > 0 && !form.value.asesor_id) {
    form.value.asesor_id = props.asesores[0].id
  }
})

const submit = async () => {
  if (anyInvalid.value) {
    toast.add({ severity: 'warn', summary: 'Faltan datos', detail: 'Revisá los campos obligatorios.', life: 3000 })
    return
  }
  loading.value = true
  errors.value = {}

  try {
    await axios.post(route('creditos.store'), {
      cliente_id: form.value.cliente_id,
      tipo_credito_id: form.value.tipo_credito_id,
      garantia_id: form.value.garantia_id,
      monto: form.value.monto,
      plazo: form.value.plazo,
      // No enviamos fecha_concesion; el servidor sólo guarda datos de pipeline
      asesor_id: form.value.asesor_id,
    })

    toast.add({ severity: 'success', summary: '¡Listo!', detail: 'Operación creada con éxito.', life: 2200 })
    setTimeout(() => router.visit(route('creditos.index')), 600)
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
      toast.add({ severity: 'error', summary: 'Validación', detail: 'Revisá los campos con error.', life: 3500 })
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo guardar la operación.', life: 3500 })
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head title="Crear Crédito"/>
  <AuthenticatedLayout>
    <Toast />
    <div class="p-6 space-y-6 max-w-5xl mx-auto">
      <div class="relative overflow-hidden rounded-2xl ring-1 ring-black/5">
        <div class="absolute inset-0 bg-gradient-to-r from-amber-500 via-rose-500 to-indigo-600"></div>
        <div class="relative p-8 lg:p-10 text-white">
          <div class="flex items-start justify-between gap-6">
            <div>
              <h1 class="text-3xl font-bold tracking-tight flex items-center gap-3">
                <Bookmark class="w-8 h-8" />
                Nueva operación
              </h1>
              <p class="mt-2 text-white/90 max-w-2xl">
                Registrá una nueva operación en el pipeline para dar seguimiento al trabajo del asesor y a las etapas del crédito.
              </p>
              <div class="mt-4">
                <Tag value="Seguimiento de pipeline" class="bg-white/10 text-white border-white/20" />
              </div>
            </div>
            <Sparkles class="w-20 h-20 opacity-80 hidden md:block" />
          </div>
        </div>
      </div>

      <Card class="rounded-2xl shadow-sm">
        <template #title>
          <div class="flex items-center gap-2">
            <BadgeDollarSign class="w-5 h-5 text-emerald-600" />
            <span>Datos de la operación</span>
          </div>
        </template>

        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <UserCircle class="w-4 h-4" /> Cliente *
              </label>
              <Dropdown
                v-model="form.cliente_id"
                :options="clientes"
                optionLabel="nombre_cliente"
                optionValue="id"
                filter
                placeholder="Seleccioná un cliente"
                class="w-full"
                :invalid="invalid.cliente_id || !!errors.cliente_id"
              />
              <small v-if="invalid.cliente_id" class="text-red-500">Seleccioná un cliente.</small>
              <small v-else-if="errors.cliente_id" class="text-red-500">{{ errors.cliente_id[0] }}</small>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <Shield class="w-4 h-4" /> Tipo de crédito *
              </label>
              <Dropdown
                v-model="form.tipo_credito_id"
                :options="tipos"
                optionLabel="nombre"
                optionValue="id"
                placeholder="Seleccioná el tipo"
                class="w-full"
                :invalid="invalid.tipo_credito_id || !!errors.tipo_credito_id"
              />
              <small v-if="invalid.tipo_credito_id" class="text-red-500">Seleccioná el tipo.</small>
              <small v-else-if="errors.tipo_credito_id" class="text-red-500">{{ errors.tipo_credito_id[0] }}</small>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <Shield class="w-4 h-4" /> Garantía *
              </label>
              <Dropdown
                v-model="form.garantia_id"
                :options="garantias"
                optionLabel="nombre"
                optionValue="id"
                placeholder="Seleccioná la garantía"
                class="w-full"
                :invalid="invalid.garantia_id || !!errors.garantia_id"
              />
              <small v-if="invalid.garantia_id" class="text-red-500">Seleccioná la garantía.</small>
              <small v-else-if="errors.garantia_id" class="text-red-500">{{ errors.garantia_id[0] }}</small>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <BadgeDollarSign class="w-4 h-4" /> Monto *
              </label>
              <InputNumber
                v-model="form.monto"
                mode="currency"
                currency="GTQ"
                locale="es-GT"
                :min="0"
                :invalid="invalid.monto || !!errors.monto"
                class="w-full"
              />
              <small v-if="invalid.monto" class="text-red-500">Ingresá un monto válido.</small>
              <small v-else-if="errors.monto" class="text-red-500">{{ errors.monto[0] }}</small>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <CalendarDays class="w-4 h-4" /> Plazo (meses) *
              </label>
              <InputNumber
                v-model="form.plazo"
                :min="1"
                :max="360"
                showButtons
                :invalid="invalid.plazo || !!errors.plazo"
                class="w-full"
              />
              <small v-if="invalid.plazo" class="text-red-500">Ingresá un plazo (≥ 1).</small>
              <small v-else-if="errors.plazo" class="text-red-500">{{ errors.plazo[0] }}</small>
            </div>

            <!-- Asesor asignado: SOLO para ADMIN -->
            <div v-if="props.isAdmin" class="space-y-2 md:col-span-2">
              <label class="text-sm font-medium flex items-center gap-2">
                <UserCircle class="w-4 h-4" /> Asesor asignado *
              </label>
              <Dropdown
                v-model="form.asesor_id"
                :options="asesores"
                optionLabel="name"
                optionValue="id"
                filter
                placeholder="Seleccioná el asesor"
                class="w-full"
                :invalid="invalid.asesor_id || !!errors.asesor_id"
              />
              <small v-if="invalid.asesor_id" class="text-red-500">Seleccioná un asesor.</small>
              <small v-else-if="errors.asesor_id" class="text-red-500">{{ errors.asesor_id?.[0] }}</small>
            </div>
          </div>

          <Divider class="my-6" />
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <Card class="rounded-xl bg-gray-50">
              <template #title>Resumen</template>
              <template #content>
                <div class="text-sm space-y-2">
                  <p>
                    <span class="text-gray-500">Cliente:</span>
                    {{ (clientes.find(c => c.id === form.cliente_id) || {}).nombre_cliente || '—' }}
                  </p>
                  <p>
                    <span class="text-gray-500">Tipo:</span>
                    {{ (tipos.find(t => t.id === form.tipo_credito_id) || {}).nombre || '—' }}
                  </p>
                  <p>
                    <span class="text-gray-500">Garantía:</span>
                    {{ (garantias.find(g => g.id === form.garantia_id) || {}).nombre || '—' }}
                  </p>
                  <p>
                    <span class="text-gray-500">Monto:</span>
                    {{ new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(form.monto || 0) }}
                  </p>
                  <p><span class="text-gray-500">Plazo:</span> {{ form.plazo || '—' }} meses</p>
                </div>
              </template>
            </Card>

            <div class="flex md:items-end justify-end gap-3">
              <Link :href="route('creditos.index')">
                <Button severity="secondary" outlined>
                  <ArrowLeft class="w-4 h-4 mr-2" />
                  Volver
                </Button>
              </Link>

              <Button :loading="loading" :disabled="loading" @click="submit" v-tooltip.bottom="'Guardar operación'">
                <Save class="w-4 h-4 mr-2" />
                Guardar
              </Button>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
:deep(.p-inputtext.p-invalid),
:deep(.p-dropdown.p-invalid .p-dropdown-label),
:deep(.p-inputnumber.p-invalid .p-inputtext) {
  border-color: #ef4444 !important;
}
</style>
