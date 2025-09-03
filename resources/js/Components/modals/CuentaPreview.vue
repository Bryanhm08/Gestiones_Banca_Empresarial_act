<script setup>
import { computed } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Divider from 'primevue/divider'
import { Wallet2, User, CalendarDays, Banknote } from 'lucide-vue-next'

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    cuenta: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['update:modelValue', 'gestionar'])

const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
})

const cu = computed(() => props.cuenta || {})
const tipo = computed(() => cu.value.tipo?.nombre ?? cu.value.tipo ?? '—')
const asesor = computed(() => cu.value.asesor?.name ?? cu.value.asesor ?? '—')
const cliente = computed(() => cu.value.cliente?.nombre_cliente ?? cu.value.cliente ?? '—')
const apertura = computed(() => cu.value.fecha_apertura ?? '—')
</script>

<template>
    <Dialog v-model:visible="open" modal :dismissableMask="true" :style="{ width: '740px', maxWidth: '95vw' }"
        class="rounded-3xl overflow-hidden" :pt="{ header: { class: 'p-0' }, content: { class: 'p-0' } }">

        <!-- Header -->
        <div class="bg-gradient-to-r from-[#1971c2] to-[#22b8cf] text-white">
            <div class="px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-11 w-11 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                        <Wallet2 class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-base font-semibold leading-tight">Cuenta #{{ cu.id ?? '—' }}</div>
                        <div class="text-xs/5 text-white/80">Cliente: {{ cliente }}</div>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-2">
                    <Tag class="bg-white/10 border-0 text-white font-medium">Tipo: {{ tipo }}</Tag>
                </div>
            </div>
        </div>
        <div class="p-5 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-2xl border border-gray-200 p-4 bg-white">
                    <div class="text-xs text-gray-500 mb-1">Cliente</div>
                    <div class="font-medium text-gray-800">{{ cliente }}</div>
                </div>
                <div class="rounded-2xl border border-gray-200 p-4 bg-white">
                    <div class="text-xs text-gray-500 mb-1">Tipo de cuenta</div>
                    <Tag class="bg-emerald-50 text-emerald-700 border-emerald-200">{{ tipo }}</Tag>
                </div>
                <div class="rounded-2xl border border-gray-200 p-4 bg-white">
                    <div class="text-xs text-gray-500 mb-1">Asesor</div>
                    <div class="font-medium text-gray-800">{{ asesor }}</div>
                </div>
            </div>

            <Divider class="!my-1" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <div class="text-xs text-gray-500 mb-2">Fechas</div>
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <dt class="col-span-1 text-gray-500">Apertura</dt>
                        <dd class="col-span-2 font-medium text-gray-800">{{ apertura }}</dd>

                        <dt class="col-span-1 text-gray-500">Creado</dt>
                        <dd class="col-span-2 font-medium text-gray-800">{{ cu.creado ?? '—' }}</dd>
                    </dl>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <div class="text-xs text-gray-500 mb-2">Identificación</div>
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <dt class="col-span-1 text-gray-500">ID interno</dt>
                        <dd class="col-span-2 font-medium text-gray-800">#{{ cu.id ?? '—' }}</dd>
                        <dt class="col-span-1 text-gray-500">Cliente ID</dt>
                        <dd class="col-span-2 font-medium text-gray-800">{{ cu.cliente?.id ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="px-5 py-4 border-t bg-white flex items-center justify-between">
            <div class="text-xs text-gray-500">CHN · Cuenta #{{ cu.id ?? '—' }}</div>
            <div class="flex items-center gap-2">
                <Button outlined @click="open = false">Cerrar</Button>
                <!-- <Button @click="$emit('gestionar', cu)">Gestionar</Button> -->
            </div>
        </div>
    </Dialog>
</template>
