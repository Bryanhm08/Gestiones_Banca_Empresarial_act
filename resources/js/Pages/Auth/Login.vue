<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

// PrimeVue
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Divider from 'primevue/divider'
import Message from 'primevue/message'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

// lucide-vue-next (iconos SVG)
import { Mail, Lock, LogIn, ShieldCheck } from 'lucide-vue-next'

defineProps({
    canResetPassword: { type: Boolean, default: true },
    status: { type: String, default: '' }
})

const form = useForm({
    email: '',
    password: '',
    remember: false
})

const submitting = ref(false)
const submit = () => {
    submitting.value = true
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password')
            submitting.value = false
        }
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
            <!-- LADO IZQUIERDO: Imagen + copy -->
            <aside class="relative hidden lg:block"
                style="background-image: url('/images/loginPicture.png'); background-size: cover; background-position: center;"
                aria-hidden="true">
                <div class="absolute inset-0 bg-black/50"></div>
                <div class="relative h-full flex flex-col justify-between p-10 text-white">
                    <div>
                        <div class="flex items-center gap-2 text-white/90">
                            <ShieldCheck class="w-5 h-5" />
                            <span class="text-sm tracking-wider">Acceso seguro</span>
                        </div>
                        <h1 class="mt-4 text-3xl font-semibold leading-tight">
                            Bienvenido a la plataforma
                        </h1>
                        <p class="mt-2 text-white/80">
                            Inicia sesión para continuar con tus operaciones.
                        </p>
                    </div>
                    <p class="text-xs text-white/60">
                        © {{ new Date().getFullYear() }} — Todos los derechos reservados.
                    </p>
                </div>
            </aside>

            <!-- LADO DERECHO: Card de login -->
            <main class="flex items-center justify-center p-6 sm:p-10">
                <Card class="w-full max-w-md shadow-2 rounded-2xl">
                    <template #title>
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-xl bg-surface-100 dark:bg-surface-800">
                                <LogIn class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold">Iniciar sesión</h2>
                                <p class="text-surface-500 text-sm">Usa tus credenciales registradas</p>
                            </div>
                        </div>
                    </template>

                    <template #content>
                        <!-- Mensaje de estado (ej. "Password reset sent" o "Password reset successful") -->
                        <Message v-if="status" severity="success" class="mb-4">
                            {{ status }}
                        </Message>

                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium mb-1">Correo electrónico</label>
                                <IconField>
                                    <InputIcon>
                                        <Mail class="w-4 h-4" />
                                    </InputIcon>
                                    <InputText
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="tucorreo@dominio.com"
                                        class="w-full"
                                        :invalid="!!form.errors.email"
                                        autocomplete="username"
                                        autofocus
                                        required
                                    />
                                </IconField>
                                <small v-if="form.errors.email" class="p-error">{{ form.errors.email }}</small>
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium mb-1">Contraseña</label>
                                <Password
                                    id="password"
                                    v-model="form.password"
                                    :feedback="false"
                                    toggleMask
                                    class="w-full"
                                    inputClass="w-full"
                                    :inputProps="{ autocomplete: 'current-password', required: true }"
                                    :pt="{
                                        panel: { class: 'hidden' }
                                    }"
                                >
                                    <template #header>
                                        <span class="text-sm">Ingresa tu contraseña</span>
                                    </template>
                                    <template #footer />
                                    <template #icon>
                                        <Lock class="w-4 h-4" />
                                    </template>
                                </Password>
                                <small v-if="form.errors.password" class="p-error">{{ form.errors.password }}</small>
                            </div>

                            <!-- Remember + Forgot -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Checkbox inputId="remember" v-model="form.remember" :binary="true" />
                                    <label for="remember" class="text-sm">Recordarme</label>
                                </div>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm underline hover:opacity-80"
                                >
                                    ¿Olvidaste tu contraseña?
                                </Link>
                            </div>

                            <Divider class="my-2" />

                            <!-- Submit -->
                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing || submitting"
                                :loading="form.processing || submitting"
                                label="Entrar"
                                iconPos="right"
                            >
                                <template #icon>
                                    <LogIn class="w-4 h-4" />
                                </template>
                            </Button>
                        </form>
                    </template>
                </Card>
            </main>
        </div>
    </GuestLayout>
</template>

<style scoped>
:deep(.p-card) {
    border-radius: 1rem;
}
</style>
