<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const togglePassword = () => {
    showPassword.value = !showPassword.value
}

const togglePasswordConfirmation = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value
}

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <BaseInput
                label="Prénom et Nom"
                name="name"
                v-model="form.name"
                required
                autofocus
                autocomplete="name"
                :error="form.errors.name"
            />
            <BaseInput
                class="mt-4"
                label="Adresse Email"
                name="email"
                type="email"
                v-model="form.email"
                required
                autocomplete="username"
                :error="form.errors.email"
            />
            <BaseInput
                class="mt-4"
                label="Mot de passe"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                required
                autocomplete="new-password"
                :error="form.errors.password"
            >
                <template #suffix>
                    <button type="button" class="app-field-suffix" @click="togglePassword">
                        <span v-if="showPassword">👁️</span>
                        <span v-else>👁️‍🗨️</span>
                    </button>
                </template>
            </BaseInput>
            <BaseInput
                class="mt-4"
                label="Confirmation Mot de passe"
                name="password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                v-model="form.password_confirmation"
                required
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
            >
                <template #suffix>
                    <button type="button" class="app-field-suffix" @click="togglePasswordConfirmation">
                        <span v-if="showPasswordConfirmation">👁️</span>
                        <span v-else>👁️‍🗨️</span>
                    </button>
                </template>
            </BaseInput>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-primary underline hover:text-primary-dark focus:outline-none focus:ring-primary focus:ring-primary focus:ring-offset-2"
                >
                    Déjà inscrit ?
                </Link>

                <PrimaryButton
                    class="ms-4 bg-primary-dark text-white hover:bg-primary-dark focus:ring-primary-dark disabled:opacity-25"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Inscrire
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
