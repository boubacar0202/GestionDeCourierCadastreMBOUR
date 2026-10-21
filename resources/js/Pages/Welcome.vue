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

const services = [
    {
        title: 'Courriers arrivées',
        text: 'Réception, référence et classement des demandes adressées au service.',
    },
    {
        title: 'Courriers départs',
        text: 'Suivi des réponses et des pièces transmises à partir du registre.',
    },
    {
        title: 'Visites',
        text: 'Enregistrement des personnes reçues au centre des services fiscaux.',
    },
];
</script>

<template>
    <Head title="Cadastre Mbour — Gestion des courriers">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>

    <div class="welcome-page">
        <header class="welcome-bar">
            <img src="/logo.jpg" alt="Direction générale des impôts et des domaines" class="welcome-logo">

            <nav v-if="canLogin" class="welcome-nav" aria-label="Accès">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="app-btn app-btn-primary"
                >
                    Accueil
                </Link>

                <template v-else>
                    <Link :href="route('login')" class="app-btn app-btn-primary">
                        Connexion
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="app-btn app-btn-secondary"
                    >
                        S'inscrire
                    </Link>
                </template>
            </nav>
        </header>

        <main class="welcome-main">
            <section class="welcome-hero">
                <p class="welcome-kicker">Centre des services fiscaux</p>
                <h1>Cadastre Mbour</h1>
                <p class="welcome-lead">
                    Gestion des courriers, des départs, des arrivées et des visites.
                </p>

                <div v-if="canLogin" class="welcome-actions">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="app-btn app-btn-primary welcome-cta"
                    >
                        Ouvrir le registre
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="app-btn app-btn-primary welcome-cta"
                    >
                        Accéder au service
                    </Link>
                </div>
            </section>

            <section class="welcome-services" aria-label="Registres du service">
                <article v-for="service in services" :key="service.title" class="welcome-card">
                    <span class="welcome-mark" aria-hidden="true"></span>
                    <h2>{{ service.title }}</h2>
                    <p>{{ service.text }}</p>
                </article>
            </section>
        </main>

        <footer class="welcome-foot">
            Programme de Gestion de Courriers <strong>MB.01</strong>
        </footer>
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
    display: flex;
    flex-direction: column;
    width: 100%;
    min-height: 100vh;
    background: #fff;
    color: #613104;
}

.welcome-bar,
.welcome-main,
.welcome-foot {
    width: min(1080px, calc(100% - 2.5rem));
    margin-inline: auto;
}

.welcome-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1.35rem 0 1.1rem;
    border-bottom: 1px solid #e6c5a2;
}

.welcome-logo {
    height: 4.25rem;
    width: auto;
    object-fit: contain;
}

.welcome-nav {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.welcome-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 3.5rem 0 2.5rem;
}

.welcome-hero {
    max-width: 38rem;
}

.welcome-kicker {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #a35200;
}

.welcome-hero h1 {
    margin-top: 0.7rem;
    font-size: clamp(2.6rem, 5vw, 4.4rem);
    line-height: 0.95;
    color: #613104;
}

.welcome-lead {
    margin-top: 1.1rem;
    max-width: 32rem;
    font-size: 1.08rem;
    line-height: 1.6;
    color: #5a3412;
}

.welcome-actions {
    margin-top: 1.6rem;
}

.welcome-cta {
    min-width: 11.5rem;
    padding: 0.75rem 1.15rem;
    font-size: 0.92rem;
}

.welcome-services {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 3.25rem;
}

.welcome-card {
    padding: 1.25rem 1.2rem 1.35rem;
    border: 1px solid #e6c5a2;
    border-radius: 1.15rem;
    background: #fff;
    box-shadow: 0 16px 36px rgba(44, 24, 16, 0.05);
}

.welcome-mark {
    display: block;
    width: 2rem;
    height: 0.28rem;
    margin-bottom: 1rem;
    border-radius: 999px;
    background: linear-gradient(90deg, #c7812e, #613104);
}

.welcome-card h2 {
    font-family: Outfit, Figtree, ui-sans-serif, system-ui, sans-serif;
    font-size: 1.02rem;
    font-weight: 700;
    letter-spacing: 0;
    color: #613104;
}

.welcome-card p {
    margin-top: 0.45rem;
    font-size: 0.92rem;
    line-height: 1.55;
    color: #5a3412;
}

.welcome-foot {
    padding: 1.1rem 0 1.4rem;
    border-top: 1px solid #e6c5a2;
    font-size: 0.84rem;
    font-weight: 600;
    color: #5a3412;
}

.welcome-foot strong {
    color: #613104;
}

@media (max-width: 760px) {
    .welcome-bar {
        flex-direction: column;
        align-items: flex-start;
    }

    .welcome-services {
        grid-template-columns: 1fr;
    }

    .welcome-main {
        padding-top: 2.4rem;
    }
}
</style>
