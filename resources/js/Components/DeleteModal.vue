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
                v-model="adminCode"
                class="mb-2 w-full"
                label="Code administrateur"
                :type="showAdminPassword ? 'text' : 'password'"
                autocomplete="off"
                placeholder="Code administrateur"
            >
                <template #suffix>
                    <button type="button" class="app-field-suffix" @click="showAdminPassword = !showAdminPassword">
                        {{ showAdminPassword ? "Masquer" : "Voir" }}
                    </button>
                </template>
            </BaseInput>

            <BaseInput
                v-model="deleteCode"
                class="mb-4 w-full"
                label="Confirmation"
                :type="showDeletePassword ? 'text' : 'password'"
                autocomplete="off"
                placeholder="Tapez SUPPRIMER"
            >
                <template #suffix>
                    <button type="button" class="app-field-suffix" @click="showDeletePassword = !showDeletePassword">
                        {{ showDeletePassword ? "Masquer" : "Voir" }}
                    </button>
                </template>
            </BaseInput>

            <p class="mb-4 text-center text-xs text-primary-txt/70">
                Tapez SUPPRIMER, puis le code administrateur.
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
