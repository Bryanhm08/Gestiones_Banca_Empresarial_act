<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CreditEditor from '@/Components/creditos/CreditEditor.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    credito: Object,
    tipos: Array,
    garantias: Array,
    estadosCatalog: Array,
    timeline: Array,
    amortizaciones: Array,
    isAdmin: Boolean,
})
</script>

<template>
    <Head title="Editar Crédito" />

    <AuthenticatedLayout>
        <CreditEditor
            :credito="props.credito"
            :tipos="props.tipos"
            :garantias="props.garantias"
            :estados-catalog="props.estadosCatalog"
            :timeline="props.timeline"
            :amortizaciones="props.amortizaciones"
            :is-admin="props.isAdmin"
        />
    </AuthenticatedLayout>
</template>
