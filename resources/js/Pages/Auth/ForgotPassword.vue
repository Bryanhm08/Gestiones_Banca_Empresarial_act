<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    status: {
        type: String,
    },
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'), {
        onFinish: () => {
            // Opcional: si quieres limpiar el campo después
            // form.reset('email')
        },
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar contraseña" />

        <div class="mb-4 text-sm text-gray-600">
            ¿Olvidaste tu contraseña? No te preocupes. Ingresa el correo electrónico con el que
            estás registrado y te enviaremos un enlace para que puedas crear una nueva contraseña.
        </div>

        <!-- Mensaje de éxito -->
        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <!-- Volver al login -->
                <Link
                    :href="route('login')"
                    class="text-sm underline text-gray-600 hover:text-gray-900"
                >
                    Volver al inicio de sesión
                </Link>

                <!-- Enviar enlace -->
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Enviar enlace de recuperación
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
