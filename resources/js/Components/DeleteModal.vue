<script setup>
import { computed, ref, watch } from "vue";
import BaseInput from "@/Components/BaseInput.vue";

const props = defineProps({
    show: { type: Boolean, required: true },
    itemName: { type: String, default: "" },
    mutation: { type: Object, default: null },
    loading: { type: Boolean, default: false },
});

const displayName = computed(() => props.itemName || props.mutation?.txt_numeroMutation || "");

const emit = defineEmits(["update:show", "confirm"]);

const deleteCode = ref("");
const adminCode = ref("");
const showAdminPassword = ref(false);
const showDeletePassword = ref(false);

watch(() => props.show, (open) => {
    if (open) {
        deleteCode.value = "";
        adminCode.value = "";
        showAdminPassword.value = false;
        showDeletePassword.value = false;
    }
});

const closeModal = () => {
    emit("update:show", false);
};

const confirmer = () => {
    if (deleteCode.value.trim().toUpperCase() !== "SUPPRIMER") {
        return;
    }

    if (!adminCode.value.trim()) {
        return;
    }

    emit("confirm", {
        deleteCode: deleteCode.value,
        adminCode: adminCode.value,
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg">
            <h2 class="mb-4 text-center text-xl font-bold text-[#9b3a2f]">
                Confirmer la suppression
            </h2>

            <p class="mb-4 text-center text-primary-txt">
                Voulez-vous vraiment supprimer :
                <br>
                <span class="font-semibold text-primary">{{ displayName }}</span>
            </p>

            <BaseInput
                v-model="deleteCode"
                class="mb-2 w-full"
                label="Confirmation"
                :type="showDeletePassword ? 'text' : 'password'"
                autocomplete="off"
                placeholder="Tapez SUPPRIMER"
            >
                <template #append>
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-primary-txt/70 hover:text-primary"
                        :aria-label="showDeletePassword ? 'Masquer la confirmation' : 'Afficher la confirmation'"
                        @click="showDeletePassword = !showDeletePassword"
                    >
                        <svg v-if="showDeletePassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.5 10.7A2 2 0 0 0 12 14a2 2 0 0 0 1.3-.5M9.9 5.2A9.8 9.8 0 0 1 12 5c5 0 8.5 3.5 10 7-1.2 2.7-3.4 4.9-6.1 6.1M6.1 6.1C4.2 7.4 2.7 9.2 2 12c.6 1.2 1.4 2.3 2.4 3.2" />
                        </svg>
                        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </template>
            </BaseInput>

            <BaseInput
                v-model="adminCode"
                class="mb-4 w-full"
                label="Code administrateur"
                :type="showAdminPassword ? 'text' : 'password'"
                autocomplete="off"
                placeholder="Code administrateur"
            >
                <template #append>
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-primary-txt/70 hover:text-primary"
                        :aria-label="showAdminPassword ? 'Masquer le code' : 'Afficher le code'"
                        @click="showAdminPassword = !showAdminPassword"
                    >
                        <svg v-if="showAdminPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.5 10.7A2 2 0 0 0 12 14a2 2 0 0 0 1.3-.5M9.9 5.2A9.8 9.8 0 0 1 12 5c5 0 8.5 3.5 10 7-1.2 2.7-3.4 4.9-6.1 6.1M6.1 6.1C4.2 7.4 2.7 9.2 2 12c.6 1.2 1.4 2.3 2.4 3.2" />
                        </svg>
                        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </template>
            </BaseInput>

            <p class="mb-4 text-center text-xs text-primary-txt/70">
                1) Tapez <strong>SUPPRIMER</strong> &nbsp; 2) Entrez le code administrateur
            </p>

            <div class="flex items-center justify-center gap-3">
                <button type="button" class="app-btn app-btn-secondary" :disabled="loading" @click="closeModal">
                    Annuler
                </button>
                <button
                    type="button"
                    class="app-btn app-btn-danger"
                    :disabled="loading || deleteCode.trim().toUpperCase() !== 'SUPPRIMER' || !adminCode.trim()"
                    @click="confirmer"
                >
                    {{ loading ? "Suppression..." : "Supprimer" }}
                </button>
            </div>
        </div>
    </div>
</template>
