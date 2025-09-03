<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, usePage, Head } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Divider from 'primevue/divider'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import { Users, UserPlus, ShieldCheck, Sparkles } from 'lucide-vue-next'

const toast = useToast()
const page = usePage()

onMounted(() => {
    const success = page.props.flash?.success
    if (success) {
        toast.add({ severity: 'success', summary: 'Éxito', detail: success, life: 2500 })
    }
})
</script>

<template>
    <Head title="Clientes"/>
    <AuthenticatedLayout>
        <Toast />
        <div class="p-6 space-y-6 max-w-7xl mx-auto">
            <div class="relative overflow-hidden rounded-2xl ring-1 ring-black/5">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-sky-500 to-emerald-500"></div>
                <div class="relative p-8 lg:p-10 text-white">
                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight flex items-center gap-3">
                                <Users class="w-8 h-8" />
                                Módulo de Clientes
                            </h1>
                            <p class="mt-2 text-white/90 max-w-2xl">
                                Gestioná personas y empresas, asigná asesores y mantené tu cartera al día con una
                                experiencia rápida y clara.
                            </p>

                            <div class="mt-6 flex flex-wrap items-center gap-3">
                                <Link :href="route('clientes.create')">
                                <Button size="large" class="!bg-white !text-gray-900 hover:!bg-white/90 transition">
                                    <UserPlus class="w-4 h-4 mr-2" />
                                    Nuevo cliente
                                </Button>
                                </Link>

                                <Tag value="Rápido y seguro" severity="success"
                                    class="bg-white/10 text-white border-white/20" />
                            </div>
                        </div>

                        <div class="hidden md:block">
                            <Sparkles class="w-24 h-24 opacity-80" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <Card class="rounded-2xl shadow-sm">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <ShieldCheck class="w-5 h-5 text-emerald-600" />
                            <span>Requisitos mínimos</span>
                        </div>
                    </template>
                    <template #content>
                        <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">
                            <li>Nombre del cliente</li>
                            <li>Categoría</li>
                            <li>NIT</li>
                            <li>Fecha de nacimiento / constitución</li>
                            <li>Teléfono</li>
                            <li>Asesor asignado</li>
                        </ul>
                        <Divider />
                        <p class="text-sm text-gray-500">
                            Podés completar o editar los datos más adelante si lo necesitás.
                        </p>
                    </template>
                </Card>

                <Card class="rounded-2xl shadow-sm lg:col-span-2">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <Sparkles class="w-5 h-5 text-indigo-600" />
                            <span>Tips rápidos</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="grid sm:grid-cols-2 gap-4 text-sm text-gray-700">
                            <div class="p-3 rounded-xl bg-gray-50">
                                <p class="font-medium">Validá el NIT antes de guardar</p>
                                <p class="text-gray-500">Evitá duplicados para mantener limpia la base de datos.</p>
                            </div>
                            <div class="p-3 rounded-xl bg-gray-50">
                                <p class="font-medium">Asigna el asesor correcto</p>
                                <p class="text-gray-500">Así se simplifica el seguimiento comercial.</p>
                            </div>
                            <div class="p-3 rounded-xl bg-gray-50">
                                <p class="font-medium">Categorías claras</p>
                                <p class="text-gray-500">Elegí la categoría legal correcta del cliente.</p>
                            </div>
                            <div class="p-3 rounded-xl bg-gray-50">
                                <p class="font-medium">Actualizá teléfonos y correos</p>
                                <p class="text-gray-500">Una buena data acelera la gestión.</p>
                            </div>
                        </div>

                        <Divider />
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('clientes.create')">
                            <Button outlined>
                                <UserPlus class="w-4 h-4 mr-2" />
                                Registrar cliente
                            </Button>
                            </Link>
                        </div>
                    </template>
                </Card>
            </div>
            <Card class="rounded-2xl shadow-sm">
                <template #title>Accesos rápidos</template>
                <template #content>
                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('clientes.create')">
                        <Button>
                            <UserPlus class="w-4 h-4 mr-2" />
                            Nuevo cliente
                        </Button>
                        </Link>
                        <Link :href="route('reportes.creditos')">
                        <Button outlined>
                            <Users class="w-4 h-4 mr-2" />
                            Reportes de créditos
                        </Button>
                        </Link>
                    </div>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
