<script setup>
import { computed } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Divider from 'primevue/divider'
import { User, Mail, Phone, CalendarDays, Copy, Shield } from 'lucide-vue-next'

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    cliente: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['update:modelValue', 'gestionar'])

const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
})

const c = computed(() => props.cliente || {})
const nombre = computed(() => c.value.nombre || c.value.nombre_cliente || 'Cliente')
const categoria = computed(() => c.value.categoria?.nombre ?? c.value.categoria ?? '—')
const asesor = computed(() => c.value.asesor?.name ?? c.value.asesor ?? '—')
const nit = computed(() => c.value.nit || '—')
const telefono = computed(() => c.value.telefono || '—')
const email = computed(() => c.value.email || '—')
const fnac = computed(() => c.value.fecha_nacimiento || '—')

const initials = (str) =>
    (str || '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(s => s[0])
        .join('')
        .toUpperCase()

const copy = async (text) => {
    try { await navigator.clipboard.writeText(text || ''); } catch { }
}
</script>

<template>
    <Dialog v-model:visible="open" modal :dismissableMask="true" :style="{ width: '760px', maxWidth: '95vw' }"
        class="rounded-3xl overflow-hidden" :pt="{ header: { class: 'p-0' }, content: { class: 'p-0' } }">

        <div class="bg-gradient-to-r from-[#0b3a5b] via-[#1971c2] to-[#22b8cf] text-white">
            <div class="px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-11 w-11 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                        <User class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-base font-semibold leading-tight">{{ nombre }}</div>
                        <div class="text-xs/5 text-white/80">NIT: {{ nit }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="telefono !== '—' ? `tel:${telefono}` : '#'"
                        class="rounded-xl bg-white/10 hover:bg-white/20 px-3 py-1.5 text-sm">Llamar</a>
                    <a :href="email !== '—' ? `mailto:${email}` : '#'"
                        class="rounded-xl bg-white/10 hover:bg-white/20 px-3 py-1.5 text-sm">Enviar correo</a>
                </div>
            </div>
        </div>

        <div class="p-5 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-2xl border border-gray-200 p-4 bg-white">
                    <div class="text-xs text-gray-500 mb-1 flex items-center gap-2">
                        <Shield class="w-4 h-4 text-gray-400" /> Categoría
                    </div>
                    <Tag class="bg-blue-50 text-blue-700 border-blue-200">{{ categoria }}</Tag>
                </div>

                <div class="rounded-2xl border border-gray-200 p-4 bg-white">
                    <div class="text-xs text-gray-500 mb-1">Asesor asignado</div>
                    <div class="font-medium text-gray-800">{{ asesor }}</div>
                </div>

                <div class="rounded-2xl border border-gray-200 p-4 bg-white">
                    <div class="text-xs text-gray-500 mb-1">ID interno</div>
                    <div class="font-medium text-gray-800">#{{ c.id ?? '—' }}</div>
                </div>
            </div>

            <Divider class="!my-1" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <div class="text-xs text-gray-500 mb-2">Contacto</div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-gray-700">
                                <Phone class="w-4 h-4 text-gray-400" />
                                <span class="font-medium">{{ telefono }}</span>
                            </div>
                            <a v-if="telefono !== '—'" :href="`tel:${telefono}`"
                                class="text-xs text-blue-700 hover:underline">Llamar</a>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-gray-700">
                                <Mail class="w-4 h-4 text-gray-400" />
                                <span class="font-medium">{{ email }}</span>
                            </div>
                            <a v-if="email !== '—'" :href="`mailto:${email}`"
                                class="text-xs text-blue-700 hover:underline">Enviar</a>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-gray-700">
                                <span
                                    class="inline-flex items-center justify-center h-5 w-5 rounded bg-gray-200 text-gray-600 text-[10px] font-semibold">
                                    {{ initials(nombre) }}
                                </span>
                                <span class="font-medium">NIT: {{ nit }}</span>
                            </div>
                            <button class="text-xs text-blue-700 hover:underline" @click="copy(nit)">Copiar</button>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <div class="text-xs text-gray-500 mb-2">Datos generales</div>
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <dt class="col-span-1 text-gray-500">Nacimiento / constitución</dt>
                        <dd class="col-span-2 font-medium text-gray-800">{{ fnac }}</dd>

                        <dt class="col-span-1 text-gray-500">Creado</dt>
                        <dd class="col-span-2 font-medium text-gray-800">{{ c.creado ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="px-5 py-4 border-t bg-white flex items-center justify-between">
            <div class="text-xs text-gray-500">CHN · Cliente #{{ c.id ?? '—' }}</div>
            <div class="flex items-center gap-2">
                <Button outlined @click="open = false">Cerrar</Button>
                <!-- <Button @click="$emit('gestionar', c)">Gestionar</Button> -->
            </div>
        </div>
    </Dialog>
</template>
