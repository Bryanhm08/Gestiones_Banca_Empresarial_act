<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
    token: {
        type: String,
        required: true,
    },
})

// El formulario debe coincidir con lo que valida NewPasswordController
const form = useForm({
    token: props.token,
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation')
        },
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Restablecer contraseña" />

        <div class="mb-4 text-sm text-gray-600">
            Ingresa tu correo electrónico y define una nueva contraseña para acceder nuevamente a la plataforma.
        </div>

        <form @submit.prevent="submit">
            <!-- Email -->
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

            <!-- Nueva contraseña -->
            <div class="mt-4">
                <InputLabel for="password" value="Nueva contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Confirmación -->
            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar contraseña" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <!-- Volver al login -->
                <Link
                    :href="route('login')"
                    class="text-sm underline text-gray-600 hover:text-gray-900"
                >
                    Volver al inicio de sesión
                </Link>

                <!-- Guardar nueva contraseña -->
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Restablecer contraseña
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
