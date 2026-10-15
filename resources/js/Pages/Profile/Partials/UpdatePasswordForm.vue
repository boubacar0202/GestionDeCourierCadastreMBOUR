<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-primary-txt">
                Update Password
            </h2>

            <p class="mt-1 text-sm text-primary-txt/80">
                Ensure your account is using a long, random password to stay
                secure.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <BaseInput
                ref="currentPasswordInput"
                label="Mot de passe actuel"
                name="current_password"
                type="password"
                v-model="form.current_password"
                autocomplete="current-password"
                :error="form.errors.current_password"
            />

            <BaseInput
                ref="passwordInput"
                label="Nouveau mot de passe"
                name="password"
                type="password"
                v-model="form.password"
                autocomplete="new-password"
                :error="form.errors.password"
            />

            <BaseInput
                label="Confirmation"
                name="password_confirmation"
                type="password"
                v-model="form.password_confirmation"
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
            />

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-primary-txt/80"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
