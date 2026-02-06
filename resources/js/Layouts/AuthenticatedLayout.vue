<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3'; 
import NavLink from '@/Components/NavLink.vue';
import { toast } from "@/Components/AppToast";  
import axios from 'axios';

// --- USER & PAGE ---
const page = usePage();
const user = page.props.auth?.user;

// --- MENU ---
const isMenuOpen = ref(false);
const isDesktop = ref(false);
const showingNavigationDropdown = ref(false);
const unreadTotal = ref(0);

const isSmallScreen = ref(false);

function updateScreenSize() {
    isSmallScreen.value = window.innerWidth < 1024; // tu peux ajuster le seuil
}

onMounted(() => {
    updateScreenSize();
    window.addEventListener('resize', updateScreenSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateScreenSize);
});
// --- TOAST --- 
onMounted(() => {
    if (page.props.flash.success) {
        toast.success(page.props.flash.success);
    }
});

// --- GESTION DE L'ÉCRAN ET DU MENU ---
function checkScreenSize() {
    isDesktop.value = window.innerWidth >= 768;
    // Sur desktop, on peut rouvrir le menu si c'était l'état précédent
    if (isDesktop.value) {
        const savedMenuState = localStorage.getItem('menuOpen');
        if (savedMenuState === 'true') {
            isMenuOpen.value = true;
        }
    }
}

function toggleMenu() {
    isMenuOpen.value = !isMenuOpen.value;
    // Sauvegarde la préférence utilisateur
    localStorage.setItem('menuOpen', isMenuOpen.value.toString());
}

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);

    // Récupère l'état sauvegardé ou utilise l'état par défaut
    const savedMenuState = localStorage.getItem('menuOpen');
    if (savedMenuState !== null) {
        isMenuOpen.value = savedMenuState === 'true';
    } else {
        // Par défaut : ouvert sur desktop, fermé sur mobile
        isMenuOpen.value = window.innerWidth >= 768;
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});

// --- PERSISTANCE AUTOMATIQUE ---
watch(isMenuOpen, (newValue) => {
    localStorage.setItem('menuOpen', newValue.toString());
});
 
// Charger le total des messages non lus au montage
onMounted(async () => {
    try {
        const res = await axios.get("/messages/unread-total");
        unreadTotal.value = res.data.total;
    } catch (e) {
        console.error("Erreur fetch unread:", e);
    }
});
 
</script>
 
<template>
 
    <div class="flex min-h-screen bg-primary-layout">
        <!-- Sidebar -->
        <nav class="w-64 h-screen bg-white border-r border-primary-only fixed flex flex-col p-4 overflow-y-auto">
            <!-- Logo -->
            <div class="flex items-center justify-center mb-6">
                <Link :href="route('dashboard')">
                    <!-- <logo class="h-10 w-auto fill-current text-gray-800" /> -->
                    <img src="/logo.jpg" alt="Logo" class="h-100 w-auto">
                </Link>
            </div>

            <!-- Navigation Links -->
            <div class="flex flex-col space-y-1">
                <!-- Bouton d'ouverture/fermeture -->
                <button @click="toggleMenu" class="p-2 bg-primary text-white rounded shadow-md">
                    ☰ Menu
                </button>
                <transition name="slide">
                    
                        <nav v-if="isMenuOpen || !isDesktop" class="flex flex-col space-y-1" >
                            <!-- Navigation Links -->
                            <div class="flex flex-col space-y-1">
                              
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('dashboard')}">
                                    <svg class="hover:white w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6d3500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    Acceuil
                                </NavLink>
                                <NavLink :href="route('arrivee.create')" :active="route().current('arrivee.create')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('arrivee.create')}">
                                    <svg class="hover:white w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6d3500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 12v3m0 0l-2-2m2 2l2-2" />
                                    </svg>
                                    Enregistrement Arrivées
                                </NavLink>
                                <NavLink :href="route('depart.create')" :active="route().current('depart.create')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('depart.create')}">
                                    <svg class="hover:white w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6d3500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 5l3 3m0 0l-3 3m3-3H13" />
                                    </svg>
                                    Enregistrement Départs
                                </NavLink>
                                <NavLink :href="route('visite.create')" :active="route().current('visite.create')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('visite.create')}">                                    
                                    <svg class="hover:white w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6d3500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                     Enregistrer Visiteur
                                </NavLink> 
                                <NavLink :href="route('instance.create')" :active="route().current('instance.create')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('instance.create')}">
                                    <svg class="hover:white w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#5f2e01">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                    Instances 
                                </NavLink>
                                <NavLink :href="route('instancearrivee.create')" :active="route().current('instancearrivee.create')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('instancearrivee.create')}">
                                    <svg class="hover:white h-6 w-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6d3500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 14H8m0 0l2-2m-2 2l2 2" />
                                    </svg>
                                    Liste des Arrivées
                                </NavLink>
                                <NavLink :href="route('instancedepart.create')" :active="route().current('instancedepart.create')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('instancedepart.create')}">
                                    <svg class="hover:white h-6 w-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6d3500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 12l3-3m0 0l3 3m-3-3v9" />
                                    </svg>
                                    Liste des Départs
                                </NavLink>
                                <NavLink :href="route('instancevisite.create')" :active="route().current('instancevisite.create')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('instancevisite.create')}">
                                    <svg class="hover:white w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6d3500">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                     liste Visiteurs 
                                </NavLink>
                                <NavLink :href="route('trimestre.create')" :active="route().current('trimestre.create')" 
                                    class="hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                    :class="{'border-primary-dark': route().current('trimestre.create')}">
                                    <svg class="hover:white w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6d3500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Rapport Trimestre 
                                </NavLink> 
                                <NavLink :href="route('message.create')" 
                                        :active="route().current('message.create')" 
                                        class="relative hover:bg-primary-dark hover:border hover:text-white hover:font-bold hover:text-1xl p-2 rounded font-bold text-primary-txt border border-l-8 flex items-center" 
                                        :class="{'border-primary-dark': route().current('message.create')}">
                                        <svg class="hover:white w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-3 12H7v-2h10v2zm0-3H7V9h10v2zm0-3H7V6h10v2z"/>
                                        </svg>
                                    Discussion
                                    <span v-if="unreadTotal > 0" class="ml-2 px-2 py-1 text-xs bg-green-500 text-white rounded-full">
                                        {{ unreadTotal }}
                                    </span>
                                </NavLink> 
  
                            </div>
                        </nav> 
                </transition>
            </div>
            <footer class="bg-white border-t border-primary-only text-center text-sm text-primary-txt mt-auto py-4">
                <b>CENTRE DES SERVICES FISCAUX</b> CADASTRE MBOUR
            </footer>
 
        </nav>

        <!-- Page Content -->
        <div class="flex-1 md:ml-64 overflow-x-auto">
            <header class="bg-white shadow flex justify-between items-center p-4 relative sticky top-0 z-30">
                <!-- Titre ou Header (si présent) -->
                <div>
                    <slot name="header"/>
                </div>
 
                    <div class="relative">
 
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="flex items-center space-x-2 px-4 py-2 bg-primary rounded-md hover:bg-primary-dark">
                            <span class="text-white font-semibold">{{ user?.name }}</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <transition name="slideUser">
                            <div v-if="showingNavigationDropdown" class="absolute right-0 mt-2 w-48 bg-white border border-primary-dark rounded shadow-md z-10">
                                <div class="px-4 py-2 text-gray-800">{{ user?.name }}</div>
                                <hr>
                                <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-primary hover:bg-primary-dark">
                                    Déconnexion
                                </Link>
                            </div>
                        </transition>
                    </div> 
            </header>

            <!-- Contenu de la p-menuage -->
                <main 
                    class="p-6 overflow-x-auto" 
                >
                    <slot/>
                </main>

        </div>
    </div>
</template>

<!--  hover:border -->

<style scoped>
    .slide-enter-active,
    .slide-leave-active {
    transition: max-height 1s ease-in-out, opacity 1s ease-in-out;
    overflow: hidden;
    }

    .slide-enter-from,
    .slide-leave-to {
    max-height: 0;
    opacity: 0;
    }

    .slide-enter-to,
    .slide-leave-from {
    max-height: 500px;
    opacity: 1;
    }
  
    /* slideUser */
    .slideUser-enter-active,
    .slideUser-leave-active {
    transition: max-height 1s ease-in-out, opacity 1s ease-in-out;
    overflow: hidden;
    }

    .slideUser-enter-from,
    .slideUser-leave-to {
    max-height: 0;
    opacity: 0;
    }

    .slideUser-enter-to,
    .slideUser-leave-from {
    max-height: 500px;
    opacity: 1;
    }
</style>
