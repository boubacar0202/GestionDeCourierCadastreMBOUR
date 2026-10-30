<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { router } from "@inertiajs/vue3";

const visible = ref(false);
const message = ref("Chargement…");

let hideTimer = null;
let removeStart = null;
let removeFinish = null;
let removeError = null;
let removeInvalid = null;

// Uniquement formulaires (pas la navigation GET du menu)
const FORM_METHODS = new Set(["post", "put", "patch", "delete"]);

const METHOD_LABELS = {
    post: "Enregistrement…",
    put: "Mise à jour…",
    patch: "Mise à jour…",
    delete: "Suppression…",
};

function show(method) {
    const key = String(method || "").toLowerCase();
    if (!FORM_METHODS.has(key)) return;

    clearTimeout(hideTimer);
    message.value = METHOD_LABELS[key] || "Chargement…";
    visible.value = true;
}

function hide() {
    if (!visible.value) return;
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
        visible.value = false;
    }, 180);
}

onMounted(() => {
    removeStart = router.on("start", (event) => {
        show(event?.detail?.visit?.method);
    });
    removeFinish = router.on("finish", () => hide());
    removeError = router.on("error", () => hide());
    removeInvalid = router.on("invalid", () => hide());
});

onUnmounted(() => {
    removeStart?.();
    removeFinish?.();
    removeError?.();
    removeInvalid?.();
    clearTimeout(hideTimer);
});
</script>

<template>
    <Teleport to="body">
        <Transition name="app-logo-loader">
            <div
                v-if="visible"
                class="app-logo-loader"
                role="status"
                aria-live="polite"
                aria-busy="true"
            >
                <div class="app-logo-loader__panel">
                    <div class="app-logo-loader__ring" aria-hidden="true" />
                    <img
                        src="/logo-01.png"
                        alt=""
                        class="app-logo-loader__logo"
                        width="120"
                        height="120"
                    />
                    <p class="app-logo-loader__text">{{ message }}</p>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.app-logo-loader {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(4px);
}

.app-logo-loader__panel {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 1.75rem 2rem 1.25rem;
    border-radius: 1.25rem;
    background: #fff;
    box-shadow:
        0 0 0 1px rgba(212, 175, 55, 0.28),
        0 18px 48px rgba(62, 43, 26, 0.12);
}

.app-logo-loader__ring {
    position: absolute;
    top: 1.1rem;
    width: 9.5rem;
    height: 9.5rem;
    border-radius: 9999px;
    border: 2px solid transparent;
    border-top-color: #d4af37;
    border-right-color: rgba(212, 175, 55, 0.35);
    animation: app-logo-spin 1s linear infinite;
}

.app-logo-loader__logo {
    position: relative;
    z-index: 1;
    width: 7.5rem;
    height: 7.5rem;
    object-fit: contain;
    border-radius: 1rem;
    animation: app-logo-pulse 1.4s ease-in-out infinite;
}

.app-logo-loader__text {
    margin: 0;
    color: #3e2b1a;
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.04em;
}

.app-logo-loader-enter-active,
.app-logo-loader-leave-active {
    transition: opacity 0.2s ease;
}

.app-logo-loader-enter-from,
.app-logo-loader-leave-to {
    opacity: 0;
}

@keyframes app-logo-spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes app-logo-pulse {
    0%,
    100% {
        transform: scale(1);
        filter: drop-shadow(0 0 0 rgba(212, 175, 55, 0));
    }
    50% {
        transform: scale(1.05);
        filter: drop-shadow(0 0 12px rgba(212, 175, 55, 0.35));
    }
}
</style>
