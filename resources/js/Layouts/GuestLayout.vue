<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="app-guest">
        <Link href="/" class="app-brand">
            <img src="/logo_logo.jpg" alt="Logo Cadastre Mbour">
        </Link>

        <div class="app-guest-card">
            <slot />
        </div>
    </div>
</template>
