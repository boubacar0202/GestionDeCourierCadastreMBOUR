<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
 
function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="login - Gestion de Courriers">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>
 
    <div class="app-guest welcome-page">
        <div class="w-full max-w-3xl px-4">
            <header class="mb-8 flex flex-col items-center gap-4 text-center">
                <img src="/logo.jpg" alt="Logo Cadastre Mbour" class="h-28 w-auto">
                <nav v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="w-full hover:text-primary-dark hover:ring-primary-only focus:outline-none focus-visible:ring-[#FF2D20] app-btn app-btn-primary welcome-page-btn"
                    >
                        Accueil
                    </Link>

                    <template v-else>
                        <Link :href="route('login')" class="w-full hover:text-primary-dark hover:ring-primary-only focus:outline-none focus-visible:ring-[#FF2D20] app-btn app-btn-primary welcome-page-btn">
                            Connexion
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="w-full hover:text-primary-dark hover:ring-primary-only focus:outline-none focus-visible:ring-[#FF2D20] app-btn app-btn-secondary welcome-page-btn"
                        >
                            S'inscrire
                        </Link>
                    </template>
                </nav>
            </header>

            <a :href="route('login')"
                            id="docs-card"
                            class="flex flex-col items-start border border-primary-txt-txt gap-6 overflow-hidden 
                            rounded-xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] 
                            transition duration-300 hover:text-primary-dark hover:ring-primary-only focus:outline-none 
                            focus-visible:ring-[#FF2D20] md:row-span-3 lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 
                            dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-primary-dark">Centre des services fiscaux</p>
                        <h1 class="mt-2 text-4xl text-primary-txt">CADASTRE MBOUR</h1>
                        <p class="mt-3 text-primary-txt/80">Gestion des courriers, départs, arrivées et visites.</p>
                    </div>
                    <svg class="size-8 shrink-0 stroke-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                    </svg>
                </div>
            </a>

            <footer class="mt-8 text-center text-sm font-semibold text-primary-txt">
                Programme de Gestion de Courriers <b>MB.01</b>
            </footer>
        </div>
    </div>
</template>

<style>
html:has(.welcome-page),
body:has(.welcome-page),
.app-shell:has(.welcome-page) {
    background: #fff;
}

.app-shell:has(.welcome-page) .app-content {
    padding: 0;
    background: #fff;
}
</style>

<style scoped>
.welcome-page {
    width: 100%;
    min-height: 100vh;
    background: #fff;
}
</style>
