<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, CalendarDays, BadgeDollarSign, Shield, User } from 'lucide-vue-next'
import Head from '@inertiajs/vue3'
const props = defineProps({
    credito: { type: Object, required: true },
})
const money = (n) => new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(Number(n || 0))
</script>

<template>
    <Head title="Visualizar Crédito"/>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Detalle del crédito #{{ credito.id }}</h2>
                <Link :href="route('creditos.index')">
                <Button outlined>
                    <ArrowLeft class="w-4 h-4 mr-2" /> Volver
                </Button>
                </Link>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <Card class="rounded-2xl shadow-sm lg:col-span-2">
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <div class="text-xs text-gray-500">Cliente</div>
                            <div class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <User class="w-4 h-4 text-gray-500" /> {{ credito.cliente }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Asignado a</div>
                            <div class="text-lg font-semibold text-gray-800">{{ credito.asesor || '—' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Tipo de crédito</div>
                            <Tag :value="credito.tipo || '—'"
                                class="mt-1 bg-indigo-50 text-indigo-700 border-indigo-200" />
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Garantía</div>
                            <Tag :value="credito.garantia || '—'"
                                class="mt-1 bg-emerald-50 text-emerald-700 border-emerald-200" />
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Monto</div>
                            <div class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <BadgeDollarSign class="w-4 h-4 text-gray-500" /> {{ money(credito.monto) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Plazo</div>
                            <div class="text-lg font-semibold text-gray-800">{{ credito.plazo }} meses</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Fecha de concesión</div>
                            <div class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <CalendarDays class="w-4 h-4 text-gray-500" /> {{ credito.fecha_concesion }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Fecha de vencimiento</div>
                            <div class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <CalendarDays class="w-4 h-4 text-gray-500" /> {{ credito.fecha_vencimiento }}
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="rounded-2xl shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <Shield class="w-4 h-4 text-gray-600" />
                        <span>Acciones</span>
                    </div>
                </template>
                <template #content>
                    <p class="text-sm text-gray-600">
                        Esta vista es de solo lectura. Si el crédito te es asignado, lo podrás editar desde el listado.
                    </p>
                    <div class="mt-4">
                        <Link :href="route('creditos.index')">
                        <Button class="w-full" outlined>Volver al listado</Button>
                        </Link>
                    </div>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
