<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch, nextTick } from 'vue';
import { Head, router as Inertia, Link as InertiaLink } from '@inertiajs/vue3';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import MazBtn from 'maz-ui/components/MazBtn';

const props = defineProps({
    departs: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0,
        }),
    },
    designations: {
        type: Array,
        default: () => [],
    },
    totalCourrier: {
        type: Number,
        default: 0,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filters = ref({
    annee: props.filters?.annee || '',
    courrier: props.filters?.courrier || '',
    prenomNom: props.filters?.prenomNom || '',
    designation: props.filters?.designation || '',
});

let suppressFilterWatch = false;

function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

const loadPage = (page = 1) => {
    Inertia.get(route('instancedepart.create'), {
        ...filters.value,
        page,
    }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
        only: ['departs'],
    });
};

const debouncedSearch = debounce((page = 1) => {
    loadPage(page);
}, 300);

watch(filters, () => {
    if (suppressFilterWatch) return;
    debouncedSearch(1);
}, { deep: true });

watch(
    () => props.filters,
    (value) => {
        suppressFilterWatch = true;
        filters.value = {
            annee: value?.annee || '',
            courrier: value?.courrier || '',
            prenomNom: value?.prenomNom || '',
            designation: value?.designation || '',
        };
        nextTick(() => {
            suppressFilterWatch = false;
        });
    },
    { deep: true }
);

const hasFilters = computed(() =>
    Object.values(filters.value).some((value) => String(value || '').trim() !== '')
);

const pagination = computed(() => {
    const links = Array.isArray(props.departs?.links) ? props.departs.links : [];
    const pages = links.length > 2 ? links.slice(1, -1) : links.filter((link) => link?.url || link?.label);

    return {
        links: pages,
        prev: props.departs?.prev_page_url || null,
        next: props.departs?.next_page_url || null,
        meta: {
            current_page: props.departs?.current_page || 1,
            per_page: props.departs?.per_page || 15,
            total: props.departs?.total || 0,
            last_page: props.departs?.last_page || 1,
            from: props.departs?.from || 0,
            to: props.departs?.to || 0,
        },
    };
});

const goToPage = (url) => {
    if (!url) return;
    const params = new URL(url, window.location.origin).searchParams;
    const next = Number(params.get('page') || 1);
    if (next) loadPage(next);
};

const prevPage = () => {
    if (pagination.value.prev) goToPage(pagination.value.prev);
};

const nextPage = () => {
    if (pagination.value.next) goToPage(pagination.value.next);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

function supprimerCourrierDepart(depart) {
    const code = prompt("Entrez le code d'accès pour confirmer la suppression :");

    if (!code) {
        alert('Aucun code saisi. Suppression annulée.');
        return;
    }

    Inertia.delete(route('instancedepart.destroy', depart.id), {
        data: { code_acces: code },
        preserveScroll: true,
        onSuccess: () => {
            if ((props.departs?.data?.length || 0) === 0 && (props.departs?.current_page || 1) > 1) {
                loadPage(props.departs?.last_page || 1);
            }
        },
        onError: (errors) => {
            alert(errors.code_acces || 'Erreur : code invalide ou suppression refusée.');
        },
    });
}
</script>

<template>
    <Head title="Instances Départs">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-primary-txt">
                Liste des Courriers Départs
            </h2>

            <p v-if="!totalCourrier" class="text-xl font-semibold leading-tight text-primary-txt">
                Aucun Courrier départ trouvé.
            </p>
        </template>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl">
                    <div class="bg-white shadow-md rounded-xl">
                        <div class="p-4 border-b bg-primary rounded-t-xl">
                            <h1 class="text-lg text-white font-semibold">Liste des courriers départs</h1>
                        </div>
                        <div class="p-6">
                            <div class="p-4 bg-primary-form border border-primary-only rounded-xl">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h1 class="text-lg text-primary-txt font-bold">
                                        Liste des Courriers Départs :
                                        <span v-if="totalCourrier > 0" class="text-primary-txt">
                                            ({{ totalCourrier }})
                                        </span>
                                        <span v-else class="text-red-600">
                                            Aucun enregistrement
                                        </span>
                                        <span v-if="hasFilters" class="text-sm font-semibold text-primary">
                                            — {{ pagination.meta.total }} avec filtres
                                        </span>
                                    </h1>

                                    <form @submit.prevent class="flex flex-wrap items-center gap-2">
                                        <BaseInput
                                            class="w-24"
                                            label="Année"
                                            name="filtre-annee"
                                            type="search"
                                            maxlength="4"
                                            placeholder="année"
                                            v-model="filters.annee"
                                        />
                                        <BaseInput
                                            class="w-36"
                                            label="N° Courrier"
                                            name="filtre-courrier"
                                            type="search"
                                            placeholder="N° Courriers"
                                            v-model="filters.courrier"
                                        />
                                        <BaseInput
                                            class="w-40"
                                            label="Prénom et nom"
                                            name="filtre-nom"
                                            type="search"
                                            placeholder="Prénom & Nom"
                                            v-model="filters.prenomNom"
                                        />
                                        <BaseSelect
                                            class="w-44"
                                            label="Catégorie"
                                            name="filtre-designation"
                                            v-model="filters.designation"
                                            :options="designations"
                                            placeholder="Tous"
                                        />
                                    </form>
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="max-h-[600px] overflow-x-auto shadow-md rounded-xl">
                                    <table class="table table-auto w-full text-base text-left text-primary-txt">
                                        <thead
                                            class="sticky top-0 z-10 text-primary-txt text-center border-l border-primary-only uppercase
                                                bg-primary-form text-primary-txt whitespace-nowrap"
                                        >
                                            <tr class="h-10">
                                                <th scope="col" class="px-6 border-l border-primary-only">N°</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Bordereau</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">N° d'ordre départ</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Caractère</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Date Courrier</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Categorie</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Référence C.Arrivée</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Nombre Pièce</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Référence</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Prénom</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Nom</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">N° Lot</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Superficie</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Situation</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">NICAD</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Objet</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Destinataire</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Date Envoi</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Référence Réception</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Observation</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Durée Traitement</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">Fichier PDF</th>
                                                <th scope="col" class="px-6 py-3 border-l border-primary-only">ACTIONS</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr
                                                v-for="(depart, index) in departs.data"
                                                :key="depart.id"
                                                class="bg-white text-primary-txt text-center font-bold whitespace-nowrap hover:bg-primary-form dark:hover:bg-primary-form"
                                            >
                                                <td class="sticky left-0 z-0 bg-white px-6 py-3 border border-primary-form">
                                                    {{ ((pagination.meta.current_page - 1) * pagination.meta.per_page) + index + 1 }}
                                                </td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_bordereaucd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_numdordrecd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_caracterecd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ formatDate(depart.dt_datecouriercd) }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_categoriecd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_referencecourierarriveecd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_nombrepiececd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_referencecd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_prenomcd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_nomcd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_numLotcd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_surfacecd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_situationcd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_nicadcd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_objetcd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_destinatairecd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ formatDate(depart.dt_dateenvoicd) }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_referencereceptioncd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_observationcd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">{{ depart.txt_dureetraitementcd || '-' }}</td>
                                                <td class="px-6 py-3 border border-primary-only">
                                                    <div v-if="depart.fichierPDFcd">
                                                        <a :href="`/fichiers/${depart.fichierPDFcd}`" target="_blank" class="text-blue-600 underline">
                                                            📄 Voir PDF
                                                        </a>
                                                    </div>
                                                    <div v-else class="font-semibold italic text-primary-txt/60">Aucun fichier PDF</div>
                                                </td>
                                                <td class="sticky right-0 z-8 px-2 py-1 border bg-white whitespace-nowrap">
                                                    <div class="flex justify-center gap-2">
                                                        <InertiaLink :href="`/depart/editdepart/${depart.id}`">
                                                            <MazBtn
                                                                size="large"
                                                                aria-label="modifier"
                                                                title="modifier"
                                                                class="h-6 w-6 text-white bg-gradient-to-r from-green-500 via-green-600 to-green-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-5 py-2.5 text-center"
                                                            >
                                                                ✏️
                                                            </MazBtn>
                                                        </InertiaLink>
                                                        <MazBtn
                                                            size="large"
                                                            color="danger"
                                                            aria-label="Supprimer"
                                                            title="Supprimer"
                                                            @click="() => supprimerCourrierDepart(depart)"
                                                            class="h-6 w-6 text-white bg-gradient-to-r from-red-500 via-red-600 to-red-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-5 py-2.5 text-center"
                                                        >
                                                            🗑
                                                        </MazBtn>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="!departs.data?.length">
                                                <td colspan="23" class="text-center py-12 text-primary-txt text-base">
                                                    Aucun courrier départ trouvé
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="p-4 border-t flex flex-wrap justify-between items-center gap-4 bg-gray-50 rounded-b-xl">
                                <p class="text-sm text-primary-txt">
                                    {{ pagination.meta.from }}–{{ pagination.meta.to }} sur {{ pagination.meta.total }}
                                </p>
                                <div class="flex gap-1 flex-wrap">
                                    <button
                                        type="button"
                                        @click="prevPage"
                                        :disabled="!pagination.prev"
                                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition disabled:opacity-40 disabled:cursor-not-allowed bg-gray-200 hover:bg-gray-300 text-primary-txt"
                                    >
                                        ← Précédent
                                    </button>

                                    <button
                                        v-for="(link, linkIndex) in pagination.links"
                                        :key="linkIndex"
                                        type="button"
                                        @click="goToPage(link.url)"
                                        :disabled="!link.url"
                                        :class="[
                                            'px-3.5 py-1.5 rounded-lg text-sm font-medium transition',
                                            link.active
                                                ? 'bg-primary text-white shadow-md'
                                                : 'bg-gray-200 hover:bg-gray-300 text-primary-txt',
                                            !link.url ? 'opacity-40 cursor-not-allowed' : ''
                                        ]"
                                        v-html="link.label"
                                    />

                                    <button
                                        type="button"
                                        @click="nextPage"
                                        :disabled="!pagination.next"
                                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition disabled:opacity-40 disabled:cursor-not-allowed bg-gray-200 hover:bg-gray-300 text-primary-txt"
                                    >
                                        Suivant →
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
table tr td {
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
    line-height: 1rem;
    font-size: 0.80rem;
}

button:active:not(:disabled) {
    transform: scale(0.95);
}
</style>
