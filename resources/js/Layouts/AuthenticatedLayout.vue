<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

import AppSidebar from './AppSidebar.vue'
import AppFooter from './AppFooter.vue'

import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

import { Search, ClipboardList, Plus, Bell, Unlock } from 'lucide-vue-next'

const showingNavigationDropdown = ref(false)
const page = usePage()
const user = page.props.auth?.user ?? null

const initials = (name) =>
  (name || '')
    .split(' ')
    .map((p) => p[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()

// 🔍 Buscador global
const globalSearch = ref('')

const submitGlobalSearch = () => {
  const term = globalSearch.value.trim()
  if (!term) return

  router.visit(route('mis.asignaciones', { q: term }))
}

// 🔔 Notificaciones (reuniones, vencimientos, etc.)
const showNotifications = ref(false)
const notifications = ref([])
const loadingNotifications = ref(false)
const notificationsError = ref(null)

const loadNotifications = async () => {
  loadingNotifications.value = true
  notificationsError.value = null

  try {
    // Reutilizamos el endpoint de calendario
    const res = await axios.get(route('calendario.events'))
    const all = res.data || []

    const now = new Date()

    // Filtrar próximos eventos (fecha >= hoy)
    const upcoming = all
      .filter((ev) => ev.start && new Date(ev.start) >= now)
      .sort((a, b) => new Date(a.start) - new Date(b.start))
      .slice(0, 5)

    notifications.value = upcoming
  } catch (e) {
    console.error('Error cargando notificaciones', e)
    notificationsError.value = 'No se pudieron cargar las notificaciones.'
  } finally {
    loadingNotifications.value = false
  }
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value

  if (showNotifications.value && notifications.value.length === 0 && !loadingNotifications.value) {
    loadNotifications()
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <AppSidebar class="hidden md:flex" />

    <!-- Contenido principal -->
    <div class="flex-1 min-w-0 flex flex-col">
      <!-- TOPBAR -->
      <header
        class="sticky top-0 z-40 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/60 border-b border-gray-200"
      >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="h-16 flex items-center justify-between gap-3">
            <!-- Logo / título -->
            <div class="flex items-center gap-2">
              <Link :href="route('dashboard')" class="flex items-center gap-2">
                <span class="hidden sm:block font-semibold tracking-wide text-gray-800">
                  CHN · SGBE
                </span>
              </Link>
            </div>

            <!-- 🔍 Buscador global -->
            <div class="hidden md:flex flex-1 justify-center">
              <div class="relative w-full max-w-xl">
                <Search
                  class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                />
                <input
                  v-model="globalSearch"
                  type="text"
                  placeholder="Buscar cliente..."
                  class="w-full h-10 pl-9 pr-3 rounded-full border border-gray-200 bg-gray-50 text-sm
                         focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400"
                  @keyup.enter="submitGlobalSearch"
                />
              </div>
            </div>

            <!-- Acciones topbar (desktop) -->
            <div class="hidden sm:flex items-center gap-2">
              <!-- Mis asignaciones -->
              <Link :href="route('mis.asignaciones')">
                <button
                  class="relative rounded-xl border border-gray-200 bg-white hover:bg-gray-50 p-2.5"
                  type="button"
                  title="Mis asignaciones"
                >
                  <ClipboardList class="w-4 h-4 text-gray-700" />
                </button>
              </Link>

              <!-- Liberaciones -->
              <Link :href="route('liberaciones.index')">
                <button
                  class="relative rounded-xl border border-gray-200 bg-white hover:bg-gray-50 p-2.5"
                  type="button"
                  title="Liberaciones"
                >
                  <Unlock class="w-4 h-4 text-gray-700" />
                </button>
              </Link>

              <!-- Nuevo crédito -->
              <Link :href="route('creditos.create')">
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm
                         font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none
                         focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <Plus class="w-4 h-4" />
                  <span>Nuevo crédito</span>
                </button>
              </Link>

              <!-- Notificaciones -->
              <div class="relative">
                <button
                  class="relative rounded-xl border border-gray-200 bg-white hover:bg-gray-50 p-2.5"
                  type="button"
                  title="Notificaciones"
                  @click="toggleNotifications"
                >
                  <Bell class="w-4 h-4 text-gray-700" />
                  <span
                    v-if="notifications.length > 0"
                    class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-red-500"
                  ></span>
                </button>

                <!-- Panel de notificaciones -->
                <transition name="fade">
                  <div
                    v-if="showNotifications"
                    class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 shadow-lg rounded-xl z-50"
                  >
                    <div class="px-4 py-3 border-b border-gray-100">
                      <p class="text-sm font-semibold text-gray-800">
                        Recordatorios
                      </p>
                      <p class="text-xs text-gray-500">
                        Reuniones y eventos próximos
                      </p>
                    </div>

                    <div class="max-h-80 overflow-y-auto">
                      <div v-if="loadingNotifications" class="px-4 py-3 text-xs text-gray-500">
                        Cargando notificaciones...
                      </div>

                      <div
                        v-else-if="notificationsError"
                        class="px-4 py-3 text-xs text-red-500"
                      >
                        {{ notificationsError }}
                      </div>

                      <div
                        v-else-if="notifications.length === 0"
                        class="px-4 py-3 text-xs text-gray-500"
                      >
                        No hay eventos próximos.
                      </div>

                      <ul v-else class="divide-y divide-gray-100 text-sm">
                        <li
                          v-for="ev in notifications"
                          :key="ev.id"
                          class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
                        >
                          <p class="font-medium text-gray-800 truncate">
                            {{ ev.title || 'Evento' }}
                          </p>
                          <p class="text-xs text-gray-500 mt-0.5">
                            {{ ev.start }}
                          </p>
                        </li>
                      </ul>
                    </div>

                    <div class="px-4 py-2 border-t border-gray-100 bg-gray-50 text-xs text-right">
                      <Link :href="route('calendario.index')" class="text-blue-600 hover:underline">
                        Ver calendario completo
                      </Link>
                    </div>
                  </div>
                </transition>
              </div>

              <!-- Perfil / usuario -->
              <Dropdown align="right" width="56">
                <template #trigger>
                  <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white
                           px-2.5 py-1.5 text-sm text-gray-700 hover:bg-gray-50 transition"
                  >
                    <span
                      class="h-8 w-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-semibold"
                    >
                      {{ initials(user?.name) }}
                    </span>
                    <span class="hidden lg:block font-medium truncate max-w-[160px]">
                      {{ user?.name }}
                    </span>
                    <svg
                      class="h-4 w-4 text-gray-500"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.24
                           4.38a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>
                </template>

                <template #content>
                  <DropdownLink :href="route('profile.edit')">
                    Perfil
                  </DropdownLink>
                  <DropdownLink :href="route('logout')" method="post" as="button">
                    Cerrar sesión
                  </DropdownLink>
                </template>
              </Dropdown>
            </div>

            <!-- Botón hamburguesa (móvil) -->
            <button
              class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-600
                     hover:text-gray-900 hover:bg-gray-100"
              type="button"
              @click="showingNavigationDropdown = !showingNavigationDropdown"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path
                  :class="{
                    hidden: showingNavigationDropdown,
                    'inline-flex': !showingNavigationDropdown
                  }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  :class="{
                    hidden: !showingNavigationDropdown,
                    'inline-flex': showingNavigationDropdown
                  }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </header>

      <!-- CONTENIDO -->
      <main class="flex-1">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <!-- Header de la página (slot) -->
          <header v-if="$slots.header" class="mb-4">
            <slot name="header" />
          </header>

          <!-- Contenido principal -->
          <section>
            <slot />
          </section>
        </div>
      </main>

      <AppFooter />
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.12s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
