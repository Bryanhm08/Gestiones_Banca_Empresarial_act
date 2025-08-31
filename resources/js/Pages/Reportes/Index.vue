<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
<template>
    <AuthenticatedLayout>
        <div class="p-6 space-y-4">
            <h1 class="text-2xl font-bold">Reportes</h1>
            <div class="flex gap-4">
                <Link :href="route('reportes.creditos')">Reportes de créditos</Link>
                <Link :href="route('reporteria.cuentas')">Reportería de cuentas</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
