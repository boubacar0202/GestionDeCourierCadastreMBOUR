<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3'; 
import NavLink from '@/Components/NavLink.vue';
import ChatWidget from '@/Components/ChatWidget.vue';
import { toast } from "@/Components/AppToast";  
import axios from 'axios';

// --- USER & PAGE ---
const page = usePage();
const user = page.props.auth?.user;

// --- MENU --- 
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');
const comptesEnAttente = computed(() => Number(page.props.pendingAccounts || 0));
const isSettingsOpen = ref(false);
const isMenuOpen = ref(false);
const isDesktop = ref(false);
const showingNavigationDropdown = ref(false);
const unreadTotal = ref(0);
const chatOpen = ref(false);

function openChatPlugin() {
    window.dispatchEvent(new CustomEvent('open-chat-plugin'));
    if (isSmallScreen.value) {
        isMenuOpen.value = false;
    }
}

function onChatUnread(event) {
    unreadTotal.value = Number(event.detail || 0);
}

function onChatState(event) {
    chatOpen.value = !!event.detail;
}

const isSmallScreen = ref(false);

function updateScreenSize() {
    isSmallScreen.value = window.innerWidth < 1024; // tu peux ajuster le seuil
}

onMounted(() => {
    updateScreenSize();
    window.addEventListener('resize', updateScreenSize);
    if (String(route().current() || '').startsWith('parametre.')) {
        isSettingsOpen.value = true;
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', updateScreenSize);
});
// --- TOAST --- 
onMounted(() => {
    if (page.props.flash?.success) {
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

// --- TOGGLE MENU --- 
function toggleMenu() {
    isMenuOpen.value = !isMenuOpen.value;
    localStorage.setItem('menuOpen', isMenuOpen.value.toString());
}

// --- ON NAV CLICK --- 
function onNavClick(event) {
    if (isSmallScreen.value && event.target.closest('a')) {
        isMenuOpen.value = false;
    }
}

// --- ON MOUNTED --- 
onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);

    if (window.innerWidth < 1024) {
        isMenuOpen.value = false;
    } else {
        const savedMenuState = localStorage.getItem('menuOpen');
        isMenuOpen.value = savedMenuState === null ? true : savedMenuState === 'true';
    }
});

// --- ON UNMOUNTED --- 
onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});

// --- WATCH IS MENU OPEN ---
watch(isMenuOpen, (newValue) => {
    localStorage.setItem('menuOpen', newValue.toString());
});
 
// --- ON MOUNTED --- 
onMounted(async () => {
    window.addEventListener('chat-unread-total', onChatUnread);
    window.addEventListener('chat-plugin-state', onChatState);
    try {
        const res = await axios.get("/messages/unread-total");
        unreadTotal.value = res.data.total;
    } catch (e) {
        console.error("Erreur fetch unread:", e);
    }
});

// --- ON UNMOUNTED --- 
onUnmounted(() => {
    window.removeEventListener('chat-unread-total', onChatUnread);
    window.removeEventListener('chat-plugin-state', onChatState);
});
 
</script>
 
<template>
 
    <div class="app-shell bg-primary-layout flex min-h-screen rounded-xl shadow-md" :class="{ 'is-menu-open': isMenuOpen }">
        <div v-if="isMenuOpen && isSmallScreen" class="app-overlay" @click="isMenuOpen = false"></div>
        <nav class="app-sidebar" :class="{ 'is-open': isMenuOpen }" @click="onNavClick">
           
            <!-- Logo -->
            <div class="flex items-center justify-center mb-6">
                <Link :href="route('dashboard')">
                    <img src="/logo.jpg" alt="Logo" class="h-20 w-auto rounded-xl object-contain">
                </Link>
            </div>

            <div class="flex flex-col space-y-1  rounded-xl shadow-md">
                <nav class="flex flex-col space-y-1">
                            <!-- Navigation Links -->
                            <div class="flex flex-col space-y-1">
                              
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('dashboard')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5z" fill="#ff7a18"/>
                                        </svg>
                                    </span>
                                    Accueil
                                </NavLink>
                                <NavLink :href="route('secretariat.create')" :active="route().current('secretariat.create') || route().current('secretariat.edit')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('secretariat.create') || route().current('secretariat.edit')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 6h7l2 2h7v10a2 2 0 0 1-2 2H4V6z" fill="#ff7a18"/>
                                            <path d="M12 11v6m-3-3h6" stroke="white" stroke-width="2"/>
                                        </svg>
                                    </span>
                                    Enregistrer Dossier
                                </NavLink>
                                <NavLink :href="route('arrivee.create')" :active="route().current('arrivee.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('arrivee.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" fill="#ff7a18"/>
                                            <path d="M22 6l-10 7L2 6" stroke="white" stroke-width="1.6"/>
                                        </svg>
                                    </span>
                                    Enregistrement Arrivées
                                </NavLink>
                                <NavLink :href="route('depart.create')" :active="route().current('depart.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('depart.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 4h16a2 2 0 012 2v9l-5 5H4a2 2 0 01-2-2V6a2 2 0 012-2z" fill="#ff7a18"/>
                                            <path d="M17 15v5l5-5h-5z" fill="#613104"/>
                                            <path d="M7 12h7m0 0-2.2-2.2M14 12l-2.2 2.2" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    Enregistrement Départs
                                </NavLink>
                                <NavLink :href="route('visite.create')" :active="route().current('visite.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('visite.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle cx="9" cy="8" r="3" fill="#ff7a18"/>
                                            <path d="M3.2 19.5c.3-3.1 2.4-5.2 5.8-5.2s5.5 2.1 5.8 5.2z" fill="#ff7a18"/>
                                            <path d="M17.2 8h1.6v2.4H21v1.6h-2.2V14h-1.6v-2h-2.2V10.4h2.2V8z" fill="#613104"/>
                                        </svg>
                                    </span>
                                    Enregistrer Visiteur
                                </NavLink>
                                <NavLink :href="route('geometre.create')" :active="route().current('geometre.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('geometre.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 21s7-4.5 7-11a7 7 0 10-14 0c0 6.5 7 11 7 11z" fill="#ff7a18"/>
                                            <circle cx="12" cy="10" r="2" fill="white"/>
                                        </svg>
                                    </span>
                                    Enquête Cadastrale
                                </NavLink>
                                <NavLink :href="route('matriceCadastrale.create')" :active="route().current('matriceCadastrale.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('matriceCadastrale.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <rect x="3" y="4" width="7" height="7" fill="#6a11cb"/>
                                            <rect x="14" y="4" width="7" height="7" fill="#ff7a18"/>
                                            <rect x="3" y="13" width="7" height="7" fill="#ff7a18"/>
                                            <rect x="14" y="13" width="7" height="7" fill="#6a11cb"/>
                                        </svg>
                                    </span>
                                    Matrice Cadastrale
                                </NavLink>
                                <NavLink :href="route('mutation.create')" :active="route().current('mutation.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('mutation.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M7 7h11l-3-3m3 3l-3 3" stroke="#ff512f" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M17 17H6l3 3m-3-3l3-3" stroke="#dd2476" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    Mutations
                                </NavLink>
                                <NavLink :href="route('lotissement.index')" :active="route().current('lotissement.index')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('lotissement.index')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <rect x="4" y="5" width="16" height="3" fill="#6a11cb"/>
                                            <rect x="4" y="10.5" width="16" height="3" fill="#ff7a18"/>
                                            <rect x="4" y="16" width="16" height="3" fill="#ff7a18"/>
                                        </svg>
                                    </span>
                                    Liste lotissements
                                </NavLink>
                                <NavLink :href="route('mutation.index')" :active="route().current('mutation.index')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('mutation.index')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 4h10v16H4z" fill="#ff7a18"/>
                                            <path d="M14 7h6v13h-6z" fill="#00c6ff"/>
                                            <path d="M7 8h4M7 12h4M7 16h4" stroke="white" stroke-width="1.5"/>
                                        </svg>
                                    </span>
                                    Registre Mutations
                                </NavLink>
                                <NavLink :href="route('instance.create')" :active="route().current('instance.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('instance.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="16" rx="2" fill="#ff7a18"/>
                                            <path d="M7 9h10M7 13h10M7 17h6" stroke="white" stroke-width="1.6"/>
                                        </svg>
                                    </span>
                                    Instances
                                </NavLink>
                                <NavLink :href="route('instancearrivee.create')" :active="route().current('instancearrivee.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('instancearrivee.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 6h16v12H4z" fill="#ff7a18"/>
                                            <path d="M4 6l8 6 8-6" stroke="white" stroke-width="1.6"/>
                                        </svg>
                                    </span>
                                    Base Courriers Arrivées
                                </NavLink>
                                <NavLink :href="route('instancedepart.create')" :active="route().current('instancedepart.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('instancedepart.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 6h16v12H4z" fill="#ff7a18"/>
                                            <path d="M12 16V8M9 11l3-3 3 3" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    Base Courriers Départs
                                </NavLink> 
                                
                                <NavLink :href="route('donnee.create')" :active="route().current('donnee.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('donnee.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <ellipse cx="12" cy="5" rx="8" ry="3" fill="#ff7a18"/>
                                            <path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5" fill="#4facfe"/>
                                            <path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6" fill="#ff7a18"/>
                                        </svg>
                                    </span>
                                    Base de Données
                                </NavLink>
                                <NavLink :href="route('plan.create')" :active="route().current('plan.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('plan.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle cx="12" cy="12" r="10" fill="#ff7a18"/>
                                            <path d="M12 2c3 3 3 17 0 20" stroke="white" stroke-width="1.2" opacity="0.8"/>
                                            <path d="M12 2c-3 3-3 17 0 20" stroke="white" stroke-width="1.2" opacity="0.8"/>
                                            <path d="M2 12h20" stroke="white" stroke-width="1.2" opacity="0.8"/>
                                            <path d="M4 8h16" stroke="white" stroke-width="0.8" opacity="0.6"/>
                                            <path d="M4 16h16" stroke="white" stroke-width="0.8" opacity="0.6"/>
                                        </svg>
                                    </span>
                                    Plan
                                </NavLink>
                                                            
                            <template v-if="isAdmin">
                                <div class="app-settings">
                                    <NavLink
                                        :href="route('parametre.admin')"
                                        class="app-nav-link"
                                        :class="{'is-active': route().current()?.startsWith('parametre.')}"
                                    >
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M19.4 15a7.8 7.8 0 0 0 .1-1l2-1.5-2-3.5-2.3.6a7.5 7.5 0 0 0-1.7-1L15 6h-6l-.5 2.6a7.5 7.5 0 0 0-1.7 1L4.5 9l-2 3.5 2 1.5a7.8 7.8 0 0 0 .1 1L2.6 17l2 3.5 2.3-.6c.5.4 1.1.7 1.7 1L9 23h6l.5-2.6c.6-.3 1.2-.6 1.7-1l2.3.6 2-3.5-2.1-2z" fill="#ff7a18"/>
                                                <circle cx="12" cy="12" r="3.2" fill="white"/>
                                            </svg>
                                        </span>
                                        Paramètres
                                    </NavLink>
                                    <button type="button" class="app-settings-toggle" :class="{ 'is-open': isSettingsOpen }" @click.stop="isSettingsOpen = !isSettingsOpen" aria-label="Ouvrir les paramètres">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path stroke="#5f2e01" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>

                                <div v-show="isSettingsOpen" class="app-nav-sub">
                                    <NavLink :href="route('parametre.admin')" class="app-nav-link" :class="{'is-active': route().current('parametre.admin')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" fill="#ff7a18"/>
                                                <path d="M4 20a8 8 0 0 1 16 0" fill="#ff7a18"/>
                                                <path d="M16.8 13.2 19.4 14.1v2.1c0 1.4-1 2.4-2.6 3-1.6-.6-2.6-1.6-2.6-3v-2.1l2.6-.9z" fill="#613104"/>
                                                <path d="M15.9 16.1 16.8 17 18.4 15.4" stroke="white" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        Administrateurs
                                        <span v-if="comptesEnAttente > 0" class="nav-badge">{{ comptesEnAttente }}</span>
                                    </NavLink>

                                    <NavLink :href="route('parametre.user')" class="app-nav-link" :class="{'is-active': route().current('parametre.user')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle cx="16.2" cy="9" r="2.5" fill="#613104"/>
                                                <path d="M12.4 19.6c.2-2.4 1.5-4 3.8-4s3.6 1.6 3.8 4z" fill="#613104"/>
                                                <circle cx="9" cy="8.4" r="3.1" fill="#ff7a18"/>
                                                <path d="M3.4 20c.3-3.2 2.5-5.4 5.6-5.4s5.3 2.2 5.6 5.4z" fill="#ff7a18"/>
                                            </svg>
                                        </span>
                                        Utilisateurs
                                    </NavLink>

                                    <NavLink :href="route('parametre.lotissement.create')" class="app-nav-link" :class="{'is-active': route().current('parametre.lotissement.create')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect x="2" y="2.5" width="15" height="13.5" rx="2" fill="#ff7a18"/>
                                                <path d="M2 9.2h15M9.5 2.5V16" stroke="white" stroke-width="1.4"/>
                                                <circle cx="18.6" cy="18.6" r="3.6" fill="#ff7a18" stroke="white" stroke-width="1.3"/>
                                                <path d="M16.8 18.6h3.6M18.6 16.8v3.6" stroke="white" stroke-width="1.6"/>
                                            </svg>
                                        </span>
                                        Ajouter Lotissements
                                    </NavLink>

                                    <NavLink :href="route('parametre.pointDensifier.createPoint')" class="app-nav-link" :class="{'is-active': route().current('parametre.pointDensifier.createPoint')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M10.5 2.2C7.9 2.2 5.8 4.3 5.8 6.9c0 3.6 3.8 8.2 4.7 9.1.9-.9 4.7-5.5 4.7-9.1 0-2.6-2.1-4.7-4.7-4.7z" fill="#ff7a18"/>
                                                <circle cx="10.5" cy="6.9" r="1.8" fill="white"/>
                                                <circle cx="18.7" cy="18.7" r="3.5" fill="#ff7a18" stroke="white" stroke-width="1.3"/>
                                                <path d="M16.9 18.7h3.6M18.7 16.9v3.6" stroke="white" stroke-width="1.5"/>
                                            </svg>
                                        </span>
                                        Ajouter Point Densifié
                                    </NavLink>

                                    <NavLink :href="route('parametre.service.create')" class="app-nav-link" :class="{'is-active': route().current('parametre.service.create') || route().current('parametre.service.edit')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M6 3h8l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" fill="#ff7a18"/>
                                                <path d="M14 3v5h5M8 13h8M8 17h6" stroke="white" stroke-width="1.4"/>
                                            </svg>
                                        </span>
                                        Recueil de textes
                                    </NavLink>                                    
                                    
                                    <NavLink :href="route('instancevisite.create')" :active="route().current('instancevisite.create')"
                                        class="app-nav-link"
                                        :class="{'is-active': route().current('instancevisite.create')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle cx="9" cy="8.4" r="3.1" fill="#ff7a18"/>
                                                <path d="M3.4 20c.3-3.2 2.5-5.4 5.6-5.4s5.3 2.2 5.6 5.4z" fill="#ff7a18"/>
                                                <circle cx="16.2" cy="9" r="2.5" fill="#613104"/>
                                                <path d="M12.8 19.6c.2-2.2 1.4-3.6 3.4-3.6s3.2 1.4 3.4 3.6z" fill="#613104"/>
                                            </svg>
                                        </span>
                                        Liste des visiteurs
                                    </NavLink>


                                    <NavLink :href="route('parametre.trimestre.create')" class="app-nav-link" :class="{'is-active': route().current('parametre.trimestre.create')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect x="3" y="5" width="18" height="16" rx="2" fill="#ff7a18"/>
                                                <path d="M3 10h18M8 3v4M16 3v4" stroke="white" stroke-width="1.6"/>
                                            </svg>
                                        </span>
                                        Rapport Trimestres
                                    </NavLink>

                                    <NavLink :href="route('parametre.bareme.index')" class="app-nav-link" :class="{'is-active': route().current('parametre.bareme.index')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M12 4v14" stroke="#ff7a18" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M8 20h8" stroke="#ff7a18" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M4.5 8h15" stroke="#ff7a18" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M4.5 8 2 14h5L4.5 8z" fill="#ff7a18"/>
                                                <path d="M19.5 8 17 14h5l-2.5-6z" fill="#ff7a18"/>
                                            </svg>
                                        </span>
                                        Barèmes Evaluation
                                    </NavLink>

                                    <NavLink :href="route('parametre.chart')" class="app-nav-link" :class="{'is-active': route().current('parametre.chart')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M4 20V4" stroke="#ff7a18" stroke-width="2"/>
                                                <path d="M4 20h16" stroke="#ff7a18" stroke-width="2"/>
                                                <path d="M7 15l3-4 3 2 4-6" stroke="#00c6ff" stroke-width="2" fill="none"/>
                                            </svg>
                                        </span>
                                        Évaluation Générale
                                    </NavLink> 
                                </div>
                            </template>
  
                            </div>
                        </nav>
            </div>
            <footer class="app-side-footer">
                <b>CENTRE DES SERVICES FISCAUX</b><br>CADASTRE MBOUR
            </footer>
        </nav>

        <div class="app-main overflow-x-auto">
            <header class="app-topbar">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" class="app-menu-btn" @click="toggleMenu" :aria-expanded="isMenuOpen" :aria-label="isMenuOpen ? 'Fermer le menu' : 'Ouvrir le menu'">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M4 6.2h16v1.8H4zm0 5h11v1.8H4zm0 5h16v1.8H4z"/>
                        </svg>
                        Menu
                    </button>
                    <div class="min-w-0">
                        <slot name="header"/>
                    </div>
                </div>

                <div v-if="user" class="relative">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="app-user-btn">
                        <span class="app-user-mark">{{ user?.name?.charAt(0)?.toUpperCase() }}</span>
                        <span>{{ user?.name }}</span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showingNavigationDropdown }" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M6.2 8.6 12 14.4l5.8-5.8 1.4 1.4L12 17.2 4.8 10z"/>
                        </svg>
                    </button>

                    <transition name="slideUser">
                        <div v-if="showingNavigationDropdown" class="app-user-menu">
                            <div class="px-4 py-2 text-sm text-primary-txt">{{ user?.name }}</div>
                            <Link :href="route('profile.edit')" class="app-user-item" @click="showingNavigationDropdown = false">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="8" r="3.2" fill="currentColor"/>
                                    <path fill="currentColor" opacity=".4" d="M5 19.2a7 7 0 0 1 14 0v1.3H5v-1.3z"/>
                                </svg>
                                Profile
                            </Link>

                            <Link :href="route('logout')" method="post" as="button" class="app-user-item is-danger">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" opacity=".28" d="M4 4.5h8.2v15H4A1.5 1.5 0 0 1 2.5 18V6A1.5 1.5 0 0 1 4 4.5z"/>
                                    <path fill="currentColor" d="M10.2 11.1h7.1l-2.2-2.2 1.3-1.3 4.6 4.6-4.6 4.6-1.3-1.3 2.2-2.2h-7.1v-2.2z"/>
                                </svg>
                                Déconnexion
                            </Link>
                        </div>
                    </transition>
                </div>
            </header>

            <main class="app-content bg-primary-layout overflow-x-auto">
                <slot/>
            </main>
        </div>
        <ChatWidget />
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
