<script setup>
import { usePage, Link, Head} from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import { FileBarChart, Wallet2 } from 'lucide-vue-next'

const page = usePage()
const ab = page.props.abilities || {}
</script>

<template>
    <Head title="Centro de Reportes"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Centro de reportes</h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Card v-if="ab.mod_credit_reports" class="rounded-2xl shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <FileBarChart class="w-5 h-5 text-gray-600" />
                        <span>Reportes de créditos</span>
                    </div>
                </template>
                <template #content>
                    <p class="text-sm text-gray-600 mb-4">
                        KPIs, vencimientos, filtro por asesor/tipo/estado, y exportación CSV.
                    </p>
                    <Link :href="route('reportes.creditos')">
                    <Button class="bg-blue-600 hover:bg-blue-700 text-white">Abrir módulo</Button>
                    </Link>
                </template>
            </Card>

            <Card v-if="ab.mod_accounts_report" class="rounded-2xl shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <Wallet2 class="w-5 h-5 text-gray-600" />
                        <span>Reportería de cuentas</span>
                    </div>
                </template>
                <template #content>
                    <p class="text-sm text-gray-600 mb-4">
                        Altas por período, por asesor y por tipo de cuenta, con exportación CSV.
                    </p>
                    <Link :href="route('reporteria.cuentas')">
                    <Button class="bg-blue-600 hover:bg-blue-700 text-white">Abrir módulo</Button>
                    </Link>
                </template>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
