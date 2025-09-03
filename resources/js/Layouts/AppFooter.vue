<script setup>
import { computed } from 'vue'
const props = defineProps({
    env: { type: String, default: '' },
    version: { type: String, default: '' },
    buildDate: { type: String, default: '' },
})

const year = new Date().getFullYear()

const envClass = computed(() => {
    switch ((props.env || '').toUpperCase()) {
        case 'PROD': return 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200'
        case 'UAT': return 'bg-amber-100 text-amber-700 ring-1 ring-amber-200'
        case 'DEV': return 'bg-sky-100 text-sky-700 ring-1 ring-sky-200'
        default: return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
    }
})
</script>

<template>
    <footer class="mt-auto">
        <div class="h-1 w-full bg-gradient-to-r from-[#0b3a5b] via-[#1971c2] to-[#22b8cf]" />
        <div class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <img src="/favicon.png" alt="CHN" class="h-6 w-6 rounded-md" />
                            <span class="font-semibold text-gray-800">CHN · Portal</span>
                        </div>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Plataforma interna para gestión de <span class="font-medium text-gray-700">Clientes, Cuentas
                                y Créditos</span>.
                            Enfoque en seguridad, trazabilidad y eficiencia operativa.
                        </p>
                    </div>
                    <nav class="grid grid-cols-2 gap-2 md:justify-items-center">
                        <div class="space-y-2">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Legal</h4>
                            <a href="#" class="block text-sm text-gray-600 hover:text-gray-900">Términos</a>
                            <a href="#" class="block text-sm text-gray-600 hover:text-gray-900">Privacidad</a>
                            <a href="#" class="block text-sm text-gray-600 hover:text-gray-900">Cookies</a>
                            <a href="#" class="block text-sm text-gray-600 hover:text-gray-900">Accesibilidad</a>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Centro de ayuda
                            </h4>
                            <a href="#" class="block text-sm text-gray-600 hover:text-gray-900">Soporte</a>
                            <a href="#" class="block text-sm text-gray-600 hover:text-gray-900">Guías de uso</a>
                            <a href="#" class="block text-sm text-gray-600 hover:text-gray-900">Novedades</a>
                        </div>
                    </nav>
                    <div class="space-y-2 md:justify-self-end">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Contacto</h4>
                        <p class="text-sm text-gray-600">
                            Mesa de ayuda TI<br />
                            <a href="mailto:soporte@chn.gob.gt"
                                class="text-blue-700 hover:underline">soporte@chn.gob.gt</a><br />
                            <span class="text-gray-500">Lun–Vie · 08:00–17:00</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200/80">
                <div
                    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs sm:text-sm text-gray-500">
                        © {{ year }} CHN — Todos los derechos reservados.
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span v-if="env" class="px-2 py-1 rounded-full font-medium" :class="envClass"
                            v-tooltip.bottom="'Entorno de ejecución'">
                            {{ env.toUpperCase() }}
                        </span>
                        <span v-if="version" class="hidden sm:inline">· Versión {{ version }}</span>
                        <span v-if="buildDate" class="hidden sm:inline">· Build {{ buildDate }}</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
