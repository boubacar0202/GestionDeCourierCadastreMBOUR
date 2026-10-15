<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="app-btn app-btn-secondary">
        <slot />
    </button>
</template>
