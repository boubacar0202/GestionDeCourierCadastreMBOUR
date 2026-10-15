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
    localStorage.setItem('menuOpen', isMenuOpen.value.toString());
}

function onNavClick(event) {
    if (isSmallScreen.value && event.target.closest('a')) {
        isMenuOpen.value = false;
    }
}

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

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});

// --- PERSISTANCE AUTOMATIQUE ---
watch(isMenuOpen, (newValue) => {
    localStorage.setItem('menuOpen', newValue.toString());
});
 
// Charger le total des messages non lus au montage
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

onUnmounted(() => {
    window.removeEventListener('chat-unread-total', onChatUnread);
    window.removeEventListener('chat-plugin-state', onChatState);
});
 
</script>
 
<template>
 
    <div class="app-shell flex min-h-screen" :class="{ 'is-menu-open': isMenuOpen }">
        <div v-if="isMenuOpen && isSmallScreen" class="app-overlay" @click="isMenuOpen = false"></div>
        <nav class="app-sidebar" :class="{ 'is-open': isMenuOpen }" @click="onNavClick">
           
            <!-- Logo -->
            <div class="flex items-center justify-center mb-6">
                <Link :href="route('dashboard')">
                    <img src="/logo.jpg" alt="Logo" class="h-20 w-auto rounded-xl object-contain">
                </Link>
            </div>

            <div class="flex flex-col space-y-1">
                <nav class="flex flex-col space-y-1">
                            <!-- Navigation Links -->
                            <div class="flex flex-col space-y-1">
                              
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('dashboard')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M12 2.7 3.1 10a1 1 0 0 0-.3.7v9.4A1.9 1.9 0 0 0 4.7 22h4.6v-6.1h5.4V22h4.6a1.9 1.9 0 0 0 1.9-1.9v-9.4a1 1 0 0 0-.3-.7L12 2.7z"/>
                                            <path fill="currentColor" opacity=".38" d="M9.3 15.9h5.4V22H9.3z"/>
                                        </svg>
                                    </span>
                                    Accueil
                                </NavLink>
                                <NavLink :href="route('arrivee.create')" :active="route().current('arrivee.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('arrivee.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" opacity=".3" d="M3.2 5.4A2.2 2.2 0 0 1 5.4 3.2h13.2a2.2 2.2 0 0 1 2.2 2.2v6.6l-2.4 2.2H5.6L3.2 12V5.4z"/>
                                            <path fill="currentColor" d="M2 13.1h5.1c.35 1.35 1.55 2.3 3 2.3h3.8c1.45 0 2.65-.95 3-2.3H22v4.2A2.7 2.7 0 0 1 19.3 20H4.7A2.7 2.7 0 0 1 2 17.3v-4.2z"/>
                                            <path fill="currentColor" d="M11.1 3.6h1.8V9l1.8-1.8 1.3 1.3L12 12.4 8 8.5l1.3-1.3L11.1 9V3.6z"/>
                                        </svg>
                                    </span>
                                    Enregistrement Arrivées
                                </NavLink>
                                <NavLink :href="route('depart.create')" :active="route().current('depart.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('depart.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M2.7 11.2 21 3.5c.7-.3 1.4.4 1.1 1.1L14.4 23c-.3.8-1.5.7-1.7-.1l-2.1-7.4-7.4-2.1c-.8-.3-.9-1.4-.5-1.8z"/>
                                            <path fill="currentColor" opacity=".4" d="m10.6 15.5 3.8 3.1-1.1-6.2-2.7 3.1z"/>
                                        </svg>
                                    </span>
                                    Enregistrement Départs
                                </NavLink>
                                <NavLink :href="route('visite.create')" :active="route().current('visite.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('visite.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="9" cy="7.5" r="3.1" fill="currentColor"/>
                                            <path fill="currentColor" opacity=".38" d="M2.8 19.3a6.2 6.2 0 0 1 12.4 0v1.2H2.8v-1.2z"/>
                                            <path fill="currentColor" d="M17.6 8h1.7v2.7h2.7v1.7h-2.7V15h-1.7v-2.6H15v-1.7h2.6V8z"/>
                                        </svg>
                                    </span>
                                    Enregistrer Visiteur
                                </NavLink>
                                <NavLink :href="route('instance.create')" :active="route().current('instance.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('instance.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" opacity=".32" d="m12 2 9 3.7L12 9.4 3 5.7 12 2z"/>
                                            <path fill="currentColor" d="m12 8.3 9 3.7-9 3.7-9-3.7 9-3.7z"/>
                                            <path fill="currentColor" opacity=".55" d="m12 14.6 9 3.7L12 22l-9-3.7 9-3.7z"/>
                                        </svg>
                                    </span>
                                    Instances
                                </NavLink>
                                <NavLink :href="route('instancearrivee.create')" :active="route().current('instancearrivee.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('instancearrivee.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" opacity=".55" d="M6.2 3.2h8.2L19.8 8v12.2a1.8 1.8 0 0 1-1.8 1.8H6.2a1.8 1.8 0 0 1-1.8-1.8V5a1.8 1.8 0 0 1 1.8-1.8z"/>
                                            <path fill="currentColor" d="M14.4 3.2V7.4a.8.8 0 0 0 .8.8h4.6"/>
                                            <path fill="currentColor" d="m7.7 13.4 2.5 2.6 5.1-5.3 1.2 1.2-6.3 6.5-3.7-3.8 1.2-1.2z"/>
                                        </svg>
                                    </span>
                                    Liste des Arrivées
                                </NavLink>
                                <NavLink :href="route('instancedepart.create')" :active="route().current('instancedepart.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('instancedepart.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" opacity=".55" d="M6 3.2h8.2L19.6 8v12.2a1.8 1.8 0 0 1-1.8 1.8H6A1.8 1.8 0 0 1 4.2 20.2V5A1.8 1.8 0 0 1 6 3.2z"/>
                                            <path fill="currentColor" d="m14.2 3.2 5.4 4.8h-4.6a.8.8 0 0 1-.8-.8V3.2z"/>
                                            <path fill="currentColor" d="M11.2 17V11l-1.8 1.8-1.2-1.2 4-4 4 4-1.2 1.2L13 11v6h-1.8z"/>
                                        </svg>
                                    </span>
                                    Liste des Départs
                                </NavLink>
                                <NavLink :href="route('instancevisite.create')" :active="route().current('instancevisite.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('instancevisite.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="8.5" cy="8" r="2.7" fill="currentColor"/>
                                            <circle cx="15.8" cy="8.7" r="2.15" fill="currentColor" opacity=".62"/>
                                            <path fill="currentColor" d="M2.8 18.8a5.5 5.5 0 0 1 11 0v1H2.8v-1z"/>
                                            <path fill="currentColor" opacity=".62" d="M13.5 19a4.5 4.5 0 0 1 7.4 0v.8h-7.4V19z"/>
                                        </svg>
                                    </span>
                                    Liste des visiteurs
                                </NavLink>
                                <NavLink :href="route('trimestre.create')" :active="route().current('trimestre.create')"
                                    class="app-nav-link"
                                    :class="{'is-active': route().current('trimestre.create')}">
                                    <span class="nav-ico">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" opacity=".3" d="M3.4 13.4h4.1V20H3.4z"/>
                                            <path fill="currentColor" opacity=".58" d="M9.9 8.6h4.2V20H9.9z"/>
                                            <path fill="currentColor" d="M16.4 4h4.2v16h-4.2z"/>
                                        </svg>
                                    </span>
                                    Rapport Trimestre
                                </NavLink>
                                                            
                            <template v-if="isAdmin">
                                <div class="app-settings">
                                    <NavLink
                                        :href="route('parametre.admin')"
                                        class="app-nav-link"
                                        :class="{'is-active': route().current()?.startsWith('parametre.')}"
                                    >
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" opacity=".35" d="M12 8.2a3.8 3.8 0 1 0 0 7.6 3.8 3.8 0 0 0 0-7.6z"/>
                                                <path fill="currentColor" d="M10.2 2.4h3.6l.5 2.2a7.4 7.4 0 0 1 1.8.8l2-1.1 2.5 2.5-1.1 2c.4.6.6 1.2.8 1.8l2.2.5v3.6l-2.2.5a7.4 7.4 0 0 1-.8 1.8l1.1 2-2.5 2.5-2-1.1a7.4 7.4 0 0 1-1.8.8l-.5 2.2h-3.6l-.5-2.2a7.4 7.4 0 0 1-1.8-.8l-2 1.1-2.5-2.5 1.1-2a7.4 7.4 0 0 1-.8-1.8l-2.2-.5V9.1l2.2-.5c.2-.6.4-1.2.8-1.8l-1.1-2 2.5-2.5 2 1.1c.6-.4 1.2-.6 1.8-.8l.5-2.2z"/>
                                            </svg>
                                        </span>
                                        Paramètres
                                    </NavLink>
                                    <button type="button" class="app-settings-toggle" :class="{ 'is-open': isSettingsOpen }" @click.stop="isSettingsOpen = !isSettingsOpen" aria-label="Ouvrir les paramètres">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M9.2 6.2 15 12l-5.8 5.8-1.4-1.4L12.2 12 7.8 7.6l1.4-1.4z"/>
                                        </svg>
                                    </button>
                                </div>

                                <div v-show="isSettingsOpen" class="app-nav-sub">
                                    <NavLink :href="route('parametre.admin')" class="app-nav-link" :class="{'is-active': route().current('parametre.admin')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="12" cy="8" r="3.2" fill="currentColor"/>
                                                <path fill="currentColor" opacity=".4" d="M5 19.2a7 7 0 0 1 14 0V21H5v-1.8z"/>
                                                <path fill="currentColor" d="m16.2 11.4 1.6.6v1.6c0 1.1-.8 1.9-2 2.4-1.2-.5-2-1.3-2-2.4v-1.6l1.6-.6z"/>
                                            </svg>
                                        </span>
                                        Administrateurs
                                        <span v-if="comptesEnAttente > 0" class="nav-badge">{{ comptesEnAttente }}</span>
                                    </NavLink>

                                    <NavLink :href="route('parametre.user')" class="app-nav-link" :class="{'is-active': route().current('parametre.user')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="9" cy="8" r="3" fill="currentColor"/>
                                                <circle cx="16.2" cy="9" r="2.2" fill="currentColor" opacity=".45"/>
                                                <path fill="currentColor" d="M3.2 19.4a5.8 5.8 0 0 1 11.6 0v1H3.2v-1z"/>
                                                <path fill="currentColor" opacity=".45" d="M13.4 19.6a4.2 4.2 0 0 1 6.8 0v.8h-6.8v-.8z"/>
                                            </svg>
                                        </span>
                                        Utilisateurs
                                    </NavLink>

                                    <NavLink :href="route('parametre.lotissement.create')" class="app-nav-link" :class="{'is-active': route().current('parametre.lotissement.create')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" opacity=".35" d="M3 10.5 12 4l9 6.5V20H3V10.5z"/>
                                                <path fill="currentColor" d="M9.2 20v-5.2h5.6V20H9.2z"/>
                                            </svg>
                                        </span>
                                        Lotissements
                                    </NavLink>

                                    <NavLink :href="route('parametre.pointDensifier.createPoint')" class="app-nav-link" :class="{'is-active': route().current('parametre.pointDensifier.createPoint')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" d="M12 2.4c-3 0-5.4 2.4-5.4 5.4 0 4 5.4 9.6 5.4 9.6s5.4-5.6 5.4-9.6c0-3-2.4-5.4-5.4-5.4z"/>
                                                <circle cx="12" cy="7.8" r="1.8" fill="currentColor" opacity=".35"/>
                                            </svg>
                                        </span>
                                        Points
                                    </NavLink>

                                    <NavLink :href="route('parametre.service.create')" class="app-nav-link" :class="{'is-active': route().current('parametre.service.create') || route().current('parametre.service.edit')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" opacity=".4" d="M6 3.2h8.2L19.6 8v12.2a1.8 1.8 0 0 1-1.8 1.8H6A1.8 1.8 0 0 1 4.2 20.2V5A1.8 1.8 0 0 1 6 3.2z"/>
                                                <path fill="currentColor" d="M8 12.2h8v1.6H8zm0 3.4h5.4v1.6H8z"/>
                                            </svg>
                                        </span>
                                        Recueil de textes
                                    </NavLink>

                                    <NavLink :href="route('parametre.trimestre.create')" class="app-nav-link" :class="{'is-active': route().current('parametre.trimestre.create')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" opacity=".35" d="M4 5.2h16A1.8 1.8 0 0 1 21.8 7v12.2A1.8 1.8 0 0 1 20 21H4a1.8 1.8 0 0 1-1.8-1.8V7A1.8 1.8 0 0 1 4 5.2z"/>
                                                <path fill="currentColor" d="M4 9.4h16v1.6H4zM8 3v4M16 3v4"/>
                                            </svg>
                                        </span>
                                        Trimestres
                                    </NavLink>

                                    <NavLink :href="route('parametre.bareme.index')" class="app-nav-link" :class="{'is-active': route().current('parametre.bareme.index')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" d="M11 3h2v15h-2zM7 20h10v2H7z"/>
                                                <path fill="currentColor" opacity=".45" d="M4 8h16v2H4zM4 8 1.5 14h5L4 8zm16 0-2.5 6h5L20 8z"/>
                                            </svg>
                                        </span>
                                        Barèmes
                                    </NavLink>

                                    <NavLink :href="route('parametre.chart')" class="app-nav-link" :class="{'is-active': route().current('parametre.chart')}">
                                        <span class="nav-ico">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor" opacity=".35" d="M4 4h2v16H4zM4 18h16v2H4z"/>
                                                <path fill="currentColor" d="M8 14h2.2v4H8zm4.2-4H14.4v8h-2.2zM16.6 7H19v11h-2.4z"/>
                                            </svg>
                                        </span>
                                        Évaluation
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
                    <button type="button" class="app-menu-btn" @click="toggleMenu" aria-label="Menu">
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

            <main class="app-content overflow-x-auto">
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
