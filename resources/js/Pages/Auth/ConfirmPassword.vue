<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false)

const togglePassword= () => {
    showPassword.value= !showPassword.value
}

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="mb-4 text-sm text-primary-txt/80">
            Ceci est une zone sécurisée de l'application. 
            Veuillez confirmer votre mot de passe avant de continuer.
        </div>

        <form @submit.prevent="submit">
            <BaseInput
                label="Mot de passe"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                required
                autofocus
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

            <div class="mt-4 flex justify-end">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Confirmer
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
