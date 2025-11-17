<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Password from 'primevue/password'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import { Shield, UserCog, UserPlus, Search } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  users: Array,
  areas: Array,
  query: String,
})

const toast = useToast()
const q = ref(props.query || '')

const filtered = computed(() => {
  const t = q.value.trim().toLowerCase()
  if (!t) return props.users
  return props.users.filter(u =>
    [u.username, u.name, u.email, u.area, u.puesto].some(v => String(v ?? '').toLowerCase().includes(t))
  )
})

const showForm = ref(false)
const editing = ref(false)
const form = ref({
  id: null,
  username: '',
  name: '',
  email: '',
  area_id: null,
  puesto: '',
  password: '',
  password_confirmation: '',
  asesor: false,
  admin: false,
  estado: true,
})

const openCreate = () => {
  editing.value = false
  form.value = {
    id: null,
    username: '',
    name: '',
    email: '',
    area_id: null,
    puesto: '',
    password: '',
    password_confirmation: '',
    asesor: false,
    admin: false,
    estado: true,
  }
  showForm.value = true
}

const openEdit = (u) => {
  editing.value = true
  form.value = {
    ...u,
    password: '',
    password_confirmation: '',
  }
  showForm.value = true
}

const firstError = (err) => {
  const bag = err?.response?.data?.errors
  if (!bag) return null
  const key = Object.keys(bag)[0]
  return Array.isArray(bag[key]) ? bag[key][0] : null
}

const submit = async () => {
  try {
    const payload = {
      username: (form.value.username || '').trim(),
      name: (form.value.name || '').trim(),
      email: (form.value.email || '').trim(),
      puesto: form.value.puesto || null,
      area_id: form.value.area_id ?? null,
      asesor: !!form.value.asesor,
      admin: !!form.value.admin,
      estado: !!form.value.estado,
    }

    // Crear usuario: contraseña requerida
    // Editar usuario: solo si el admin escribe una nueva, se envía.
    if (!editing.value || (form.value.password && form.value.password.length > 0)) {
      payload.password = form.value.password || ''
      payload.password_confirmation = form.value.password_confirmation || ''
    }

    if (editing.value) {
      await axios.post(route('admin.users.update', form.value.id), payload)
      toast.add({ severity: 'success', summary: 'Usuario actualizado', life: 1600 })
    } else {
      await axios.post(route('admin.users.store'), payload)
      toast.add({ severity: 'success', summary: 'Usuario creado', life: 1600 })
    }

    router.reload({ only: ['users'] })
    showForm.value = false
  } catch (e) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: firstError(e) ?? 'Revisá los campos.',
      life: 2800,
    })
  }
}

const toggleEstado = async (user) => {
  try {
    await axios.patch(route('admin.users.toggle', user.id))
    toast.add({ severity: 'success', summary: 'Estado actualizado', life: 1500 })
    router.reload({ only: ['users'] })
  } catch (e) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'No se pudo cambiar el estado del usuario.',
      life: 2500,
    })
  }
}

const toggleRoles = async (user, role) => {
  try {
    const newAsesor = role === 'asesor' ? !user.asesor : user.asesor
    const newAdmin = role === 'admin' ? !user.admin : user.admin

    await axios.patch(route('admin.users.roles', user.id), {
      asesor: newAsesor,
      admin: newAdmin,
    })

    toast.add({ severity: 'success', summary: 'Roles actualizados', life: 1500 })
    router.reload({ only: ['users'] })
  } catch (e) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'No se pudieron actualizar los roles.',
      life: 2500,
    })
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <Toast />

    <template #header>
      <div class="flex items-center gap-2">
        <Shield class="w-5 h-5 text-gray-600" />
        <h2 class="text-xl font-semibold text-gray-800">Administración · Usuarios</h2>
      </div>
    </template>

    <Card class="rounded-2xl shadow-sm mb-4">
      <template #content>
        <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
          <div class="relative">
            <InputText v-model="q" placeholder="Buscar usuario, email, área..." class="pl-9 w-72" />
            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          </div>
          <div class="flex-1"></div>
          <Button @click="openCreate">
            <UserPlus class="w-4 h-4 mr-2" /> Nuevo usuario
          </Button>
        </div>
      </template>
    </Card>

    <Card class="rounded-2xl shadow-sm">
      <template #content>
        <DataTable
          :value="filtered"
          paginator
          :rows="10"
          :rowsPerPageOptions="[10, 20, 50]"
          dataKey="id"
          class="text-sm"
        >
          <Column field="username" header="Usuario" sortable />
          <Column field="name" header="Nombre" sortable />
          <Column field="email" header="Email" sortable />
          <Column field="area" header="Área" sortable />
          <Column field="puesto" header="Puesto" />
          <Column header="Roles" style="width: 210px">
            <template #body="{ data }">
              <div class="flex items-center gap-2">
                <Button
                  size="small"
                  outlined
                  :severity="data.asesor ? undefined : 'secondary'"
                  @click="toggleRoles(data, 'asesor')"
                >
                  Asesor: {{ data.asesor ? 'Sí' : 'No' }}
                </Button>
                <Button
                  size="small"
                  outlined
                  :severity="data.admin ? 'success' : 'secondary'"
                  @click="toggleRoles(data, 'admin')"
                >
                  Admin: {{ data.admin ? 'Sí' : 'No' }}
                </Button>
              </div>
            </template>
          </Column>
          <Column header="Estado" style="width: 120px">
            <template #body="{ data }">
              <Tag
                :class="
                  data.estado
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-rose-50 text-rose-700 border-rose-200'
                "
              >
                {{ data.estado ? 'Activo' : 'Inactivo' }}
              </Tag>
            </template>
          </Column>
          <Column header="Acciones" style="width: 200px">
            <template #body="{ data }">
              <div class="flex gap-2">
                <Button size="small" outlined @click="openEdit(data)">
                  <UserCog class="w-4 h-4 mr-2" /> Editar
                </Button>
                <Button size="small" outlined severity="danger" @click="toggleEstado(data)">
                  {{ data.estado ? 'Deshabilitar' : 'Habilitar' }}
                </Button>
              </div>
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Modal Crear/Editar -->
    <Dialog
      v-model:visible="showForm"
      modal
      :style="{ width: '720px', maxWidth: '95vw' }"
      class="rounded-2xl overflow-hidden"
    >
      <template #header>
        <div class="flex items-center gap-2">
          <UserCog class="w-5 h-5 text-gray-600" />
          <span class="font-semibold">{{ editing ? 'Editar usuario' : 'Nuevo usuario' }}</span>
        </div>
      </template>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-xs text-gray-500">Usuario</label>
          <InputText v-model="form.username" class="w-full" />
        </div>
        <div>
          <label class="text-xs text-gray-500">Nombre</label>
          <InputText v-model="form.name" class="w-full" />
        </div>
        <div>
          <label class="text-xs text-gray-500">Email</label>
          <InputText v-model="form.email" class="w-full" />
        </div>
        <div>
          <label class="text-xs text-gray-500">Puesto</label>
          <InputText v-model="form.puesto" class="w-full" />
        </div>
        <div>
          <label class="text-xs text-gray-500">Área</label>
          <Dropdown
            v-model="form.area_id"
            :options="props.areas"
            optionLabel="nombre"
            optionValue="id"
            class="w-full"
            placeholder="Seleccioná..."
          />
        </div>

        <!-- Contraseña -->
        <div>
          <label class="text-xs text-gray-500">
            Contraseña
            <span class="text-gray-400" v-if="editing">
              (dejar en blanco para no cambiar)
            </span>
          </label>
          <Password v-model="form.password" :feedback="false" toggleMask class="w-full" />
        </div>

        <!-- Confirmación contraseña -->
        <div>
          <label class="text-xs text-gray-500">
            Confirmar contraseña
            <span class="text-gray-400" v-if="editing">
              (solo si ingresás una nueva)
            </span>
          </label>
          <Password v-model="form.password_confirmation" :feedback="false" toggleMask class="w-full" />
        </div>

        <div class="md:col-span-2">
          <div class="flex flex-wrap gap-2">
            <Button
              outlined
              :severity="form.asesor ? undefined : 'secondary'"
              @click="form.asesor = !form.asesor"
            >
              Asesor: {{ form.asesor ? 'Sí' : 'No' }}
            </Button>
            <Button
              outlined
              :severity="form.admin ? 'success' : 'secondary'"
              @click="form.admin = !form.admin"
            >
              Admin: {{ form.admin ? 'Sí' : 'No' }}
            </Button>
            <Button
              outlined
              :severity="form.estado ? 'success' : 'secondary'"
              @click="form.estado = !form.estado"
            >
              Activo: {{ form.estado ? 'Sí' : 'No' }}
            </Button>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center gap-2">
          <Button outlined @click="showForm = false">Cancelar</Button>
          <Button @click="submit">{{ editing ? 'Guardar cambios' : 'Crear' }}</Button>
        </div>
      </template>
    </Dialog>
  </AuthenticatedLayout>
</template>
