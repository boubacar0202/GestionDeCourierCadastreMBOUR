<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-4 text-sm text-primary">
            Vous avez oublié votre mot de passe ?
            Indiquez votre adresse e-mail et nous vous enverrons un 
            lien de réinitialisation qui vous permettra d’en choisir un nouveau. 
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600"
        >
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

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    class="ms-4 bg-primary-dark text-white hover:bg-primary-dark focus:ring-primary-dark disabled:opacity-25"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Lien de réinitialisation du mot de passe par e-mail
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
