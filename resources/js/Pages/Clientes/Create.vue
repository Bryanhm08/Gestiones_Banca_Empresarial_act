<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { Head } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import InputMask from 'primevue/inputmask'
import Button from 'primevue/button'
import Toast from 'primevue/toast'
import Divider from 'primevue/divider'
import { User, IdCard, Mail, Phone, Calendar as CalendarIcon, Building2, UserCog, Save, RotateCcw } from 'lucide-vue-next'
const props = defineProps({
    categorias: { type: Array, default: () => [] },
    asesores: { type: Array, default: () => [] },
})
const toast = useToast()
const form = ref({
    nombre_cliente: '',
    categoria_id: null,
    nit: '',
    fecha_nacimiento: null,
    telefono: '',
    email: '',
    asesor_id: null,
})

const errors = ref({})
const loading = ref(false)
const required = (v) => (v !== null && v !== undefined && String(v).trim() !== '')
const isEmail = (v) => !v || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)

const invalid = computed(() => ({
    nombre_cliente: !required(form.value.nombre_cliente),
    categoria_id: !required(form.value.categoria_id),
    nit: !required(form.value.nit),
    fecha_nacimiento: !form.value.fecha_nacimiento,
    telefono: !required(form.value.telefono),
    email: !isEmail(form.value.email),
    asesor_id: !required(form.value.asesor_id),
}))

const anyInvalid = computed(() => Object.values(invalid.value).some(Boolean))

watch(form, () => { errors.value = {} }, { deep: true })

const formatDate = (dateObj) => {
    if (!dateObj) return null
    const pad = (n) => String(n).padStart(2, '0')
    const yyyy = dateObj.getFullYear()
    const mm = pad(dateObj.getMonth() + 1)
    const dd = pad(dateObj.getDate())
    return `${yyyy}-${mm}-${dd}`
}

const resetForm = () => {
    form.value = {
        nombre_cliente: '',
        categoria_id: null,
        nit: '',
        fecha_nacimiento: null,
        telefono: '',
        email: '',
        asesor_id: null,
    }
    errors.value = {}
    toast.add({ severity: 'info', summary: 'Formulario reiniciado', detail: 'Podés volver a ingresar los datos', life: 2500 })
}

const submit = async () => {
    if (anyInvalid.value) {
        toast.add({ severity: 'warn', summary: 'Faltan datos', detail: 'Revisá los campos resaltados.', life: 3000 })
        return
    }

    loading.value = true
    errors.value = {}

    try {
        const payload = {
            ...form.value,
            fecha_nacimiento: formatDate(form.value.fecha_nacimiento),
        }

        await axios.post(route('clientes.store'), payload)

        toast.add({ severity: 'success', summary: '¡Cliente creado!', detail: 'El registro se guardó correctamente.', life: 2500 })
        setTimeout(() => {
            window.location.href = route('clientes.index')
        }, 600)
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {}
            toast.add({ severity: 'error', summary: 'Errores de validación', detail: 'Revisá los campos con errores.', life: 3500 })
        } else {
            toast.add({ severity: 'error', summary: 'Error inesperado', detail: 'No se pudo guardar. Intentá de nuevo.', life: 3500 })
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <Head title="Crear Cuenta"/>
    <AuthenticatedLayout>
        <Toast />

        <div class="p-6 max-w-5xl mx-auto">
            <Card class="shadow-md rounded-2xl">
                <template #title>
                    <div class="flex items-center gap-3">
                        <User class="w-6 h-6" />
                        <span class="text-xl font-semibold">Nuevo Cliente</span>
                    </div>
                </template>

                <template #subtitle>
                    Completá los datos y asigná un asesor. Los campos con * son obligatorios.
                </template>

                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-sm font-medium flex items-center gap-2">
                                <User class="w-4 h-4" /> Nombre del cliente *
                            </label>
                            <InputText v-model="form.nombre_cliente"
                                :invalid="invalid.nombre_cliente || !!errors.nombre_cliente"
                                placeholder="Ej. Comercial XYZ, S.A." class="w-full" />
                            <small v-if="invalid.nombre_cliente" class="text-red-500">Este campo es requerido.</small>
                            <small v-else-if="errors.nombre_cliente" class="text-red-500">{{ errors.nombre_cliente[0]
                            }}</small>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium flex items-center gap-2">
                                <Building2 class="w-4 h-4" /> Categoría *
                            </label>
                            <Dropdown v-model="form.categoria_id" :options="props.categorias" optionLabel="nombre"
                                optionValue="id" placeholder="Seleccioná una categoría" class="w-full"
                                :invalid="invalid.categoria_id || !!errors.categoria_id"
                                :pt="{ root: { class: 'w-full' } }" />
                            <small v-if="invalid.categoria_id" class="text-red-500">Seleccioná una categoría.</small>
                            <small v-else-if="errors.categoria_id" class="text-red-500">{{ errors.categoria_id[0]
                            }}</small>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium flex items-center gap-2">
                                <IdCard class="w-4 h-4" /> NIT *
                            </label>
                            <InputText v-model="form.nit" :invalid="invalid.nit || !!errors.nit"
                                placeholder="Ej. 1234567-8" class="w-full uppercase" />
                            <small v-if="invalid.nit" class="text-red-500">Ingresá el NIT.</small>
                            <small v-else-if="errors.nit" class="text-red-500">{{ errors.nit[0] }}</small>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium flex items-center gap-2">
                                <CalendarIcon class="w-4 h-4" /> Fecha de nacimiento / constitución *
                            </label>
                            <Calendar v-model="form.fecha_nacimiento" dateFormat="yy-mm-dd" :showIcon="true"
                                showButtonBar class="w-full"
                                :invalid="invalid.fecha_nacimiento || !!errors.fecha_nacimiento" />
                            <small v-if="invalid.fecha_nacimiento" class="text-red-500">Seleccioná la fecha.</small>
                            <small v-else-if="errors.fecha_nacimiento" class="text-red-500">{{
                                errors.fecha_nacimiento[0] }}</small>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium flex items-center gap-2">
                                <Phone class="w-4 h-4" /> Teléfono *
                            </label>
                            <InputMask v-model="form.telefono" mask="9999-9999" placeholder="0000-0000" class="w-full"
                                :invalid="invalid.telefono || !!errors.telefono" />
                            <small v-if="invalid.telefono" class="text-red-500">Ingresá un teléfono.</small>
                            <small v-else-if="errors.telefono" class="text-red-500">{{ errors.telefono[0] }}</small>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium flex items-center gap-2">
                                <Mail class="w-4 h-4" /> Email
                            </label>
                            <InputText v-model="form.email" placeholder="correo@dominio.com" class="w-full"
                                :invalid="invalid.email || !!errors.email" />
                            <small v-if="invalid.email" class="text-red-500">Correo inválido.</small>
                            <small v-else-if="errors.email" class="text-red-500">{{ errors.email[0] }}</small>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium flex items-center gap-2">
                                <UserCog class="w-4 h-4" /> Asesor asignado *
                            </label>
                            <Dropdown v-model="form.asesor_id" :options="props.asesores" optionLabel="name"
                                optionValue="id" placeholder="Seleccioná el asesor" class="w-full"
                                :invalid="invalid.asesor_id || !!errors.asesor_id" />
                            <small v-if="invalid.asesor_id" class="text-red-500">Seleccioná un asesor.</small>
                            <small v-else-if="errors.asesor_id" class="text-red-500">{{ errors.asesor_id[0] }}</small>
                        </div>
                    </div>

                    <Divider class="my-6" />

                    <div class="flex items-center justify-end gap-3">
                        <Button severity="secondary" outlined :disabled="loading" @click="resetForm"
                            v-tooltip.bottom="'Limpiar formulario'">
                            <template #icon>
                                <RotateCcw class="w-4 h-4" />
                            </template>
                            <span class="ml-2">Limpiar</span>
                        </Button>

                        <Button :loading="loading" :disabled="loading" @click="submit">
                            <template #icon>
                                <Save class="w-4 h-4" />
                            </template>
                            <span class="ml-2">Guardar</span>
                        </Button>
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

button{
    width: 100% !important;
}
</style>
