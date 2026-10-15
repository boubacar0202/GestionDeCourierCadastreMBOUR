<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const togglePassword = () =>{
    showPassword.value = !showPassword.value
}

const togglePasswordConfirmation = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value
}
const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

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
                <PrimaryButton
                    class="ms-4 bg-primary-dark text-white hover:bg-primary-dark focus:ring-primary-dark disabled:opacity-25"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Réinitialiser le mot de passe
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
