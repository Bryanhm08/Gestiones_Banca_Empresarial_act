<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import AppSidebar from './AppSidebar.vue'
import AppFooter from './AppFooter.vue'

import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

import { Search, ClipboardList, Plus, Bell } from 'lucide-vue-next'

const showingNavigationDropdown = ref(false)
const page = usePage()
const user = page.props.auth.user

const initials = (name) =>
    (name || '').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase()
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <AppSidebar class="hidden md:flex" />
        <div class="flex-1 min-w-0 flex flex-col">
            <header
                class="sticky top-0 z-40 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/60 border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="h-16 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <span class="hidden sm:block font-semibold tracking-wide text-gray-800">CHN</span>
                            </Link>
                        </div>
                        <div class="hidden md:flex flex-1 justify-center">
                            <div class="relative w-full max-w-xl">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input type="text" placeholder="Buscar…"
                                    class="w-full h-10 pl-9 pr-3 rounded-xl bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400" />
                            </div>
                        </div>
                        <div class="hidden sm:flex items-center gap-2">
                            <Link :href="route('mis.asignaciones')">
                            <button class="relative rounded-xl border border-gray-200 bg-white hover:bg-gray-50 p-2.5"
                                p-tooltip.bottom="'Mis asignaciones'" type="button">
                                <ClipboardList class="w-4 h-4 text-gray-700" />
                            </button>
                            </Link>
                            <Link :href="route('creditos.create')">
                            <button
                                class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 flex items-center gap-2"
                                p-tooltip.bottom="'Nuevo crédito'" type="button">
                                <Plus class="w-4 h-4" />
                                <span class="hidden md:inline text-sm font-medium">Nuevo crédito</span>
                            </button>
                            </Link>
                            <button class="relative rounded-xl border border-gray-200 bg-white hover:bg-gray-50 p-2.5"
                                p-tooltip.bottom="'Notificaciones'" type="button">
                                <Bell class="w-4 h-4 text-gray-700" />
                                <span class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-rose-500"></span>
                            </button>

                            <Dropdown align="right" width="56">
                                <template #trigger>
                                    <button type="button"
                                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 px-2.5 py-1.5 text-sm text-gray-700 transition">
                                        <span
                                            class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-600 to-cyan-500 text-white flex items-center justify-center text-xs font-semibold">
                                            {{ initials(user?.name) }}
                                        </span>
                                        <span class="hidden lg:block font-medium">{{ user?.name }}</span>
                                        <svg class="-me-0.5 ms-1 h-4 w-4 text-gray-500" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.24 4.38a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Cerrar sesión
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                        <button
                            class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100"
                            @click="showingNavigationDropdown = !showingNavigationDropdown" type="button">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <path
                                    :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="md:hidden" :class="showingNavigationDropdown ? 'block' : 'hidden'">
                    <div class="px-4 pb-3">
                        <div class="text-sm text-gray-900">{{ user?.name }}</div>
                        <div class="text-xs text-gray-600">{{ user?.email }}</div>
                    </div>
                    <div class="px-4 pb-3 flex items-center gap-2">
                        <Link :href="route('mis.asignaciones')" class="flex-1">
                        <button class="w-full rounded-lg border bg-white py-2 text-sm">Mis asignaciones</button>
                        </Link>
                        <Link :href="route('creditos.create')" class="flex-1">
                        <button class="w-full rounded-lg bg-blue-600 text-white py-2 text-sm">Nuevo crédito</button>
                        </Link>
                    </div>
                </div>
            </header>
            <div v-if="$slots.header" class="bg-white border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <slot name="header" />
                </div>
            </div>
            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <slot />
                </div>
            </main>
            <AppFooter env="UAT" version="v1.0.0" buildDate="2025-09-01" />
        </div>
    </div>
</template>
