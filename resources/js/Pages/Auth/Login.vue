<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'

const showPassword = ref(false) 
 
const togglePassword = () => {
    showPassword.value = !showPassword.value
}
 
defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <BaseInput
                label="Adresse Email"
                name="email"
                type="email"
                v-model="form.email"
                required
                autofocus
                autocomplete="username"
                :error="form.errors.email"
            />

            <BaseInput
                class="mt-4"
                label="Mot de Passe"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                required
                autocomplete="current-password"
                :error="form.errors.password"
            >
                <template #suffix>
                    <button type="button" class="app-field-suffix" @click="togglePassword">
                        <span v-if="showPassword">👁️</span>
                        <span v-else>👁️‍🗨️</span>
                    </button>
                </template>
            </BaseInput>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="text-primary" />
                    <span class="ms-2 text-sm text-primary"
                        >Se souvenir de moi</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-primary underline hover:text-primary-dark focus:outline-none focus:ring-primary focus:ring-primary focus:ring-offset-2"
                >
                    Mot de passe oublié ?
                </Link>

                <PrimaryButton
                    class="ms-4 bg-primary-dark text-white hover:bg-primary-dark focus:ring-primary-dark disabled:opacity-25"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Se connecter
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
