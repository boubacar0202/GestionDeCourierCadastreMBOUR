<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed  } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import { router } from '@inertiajs/vue3'
import { Link as InertiaLink } from '@inertiajs/vue3'
  
// import { toast } from 'vue3-toastify' 
const props = defineProps({

    arrivee: Object,    
    arrivees: Array,

    departs: Array,
    depart: Object,
 
    trimestres: Array,
    countsByTrimestre: Object,
    countsTraiterByTrimestre: Object,
    stockFin: Object,
    courriersRetourners: Object,
    instancesRestantes: Object,
 
});
 
const designations = [
    "Morcellements",
    "Réquisition d\'immatriculation",
    'Demande Avis Technique',
    "Demande de terrain/Echange",
    "Prospection de terrain",
    "Autorisation de construction",
    "Autorisation de lotir",
    "Demande d\'états des lieux",
    "Demande de délimitation",
    "Demande de reconstruction",
    "Réquisition DSCOS", 
    "Tribunal",
    "Litiges",
    "Demande d\'extraits de plan",
    "Demande de situation foncière", 
    "Demande de Cession définitive a Titre Gratuit",
    "Demande de Régularisation",
    "Demande d\'attestation du Cadastre",
    "Projets de Lotissements reçus",
    "Réceptions de lotissements",
    "Lotissements réalisés sans respect des procédures",
    "Duplication de CIC",
    "Demande de Titre foncier",
    "Autirisationde morceler",
    "Demande d\'évaluation",
    "Nombre de fiches de mise à jour reçues",
    "Nombre de dossiers techniques en attente de fiches de mise à jour",
]; 
  
// Fonction pour obtenir le trimestre d’un mois
const currentTrimestre = (mois) => {
    if ([1, 2, 3].includes(mois)) return '1er trimestre';
    if ([4, 5, 6].includes(mois)) return '2ème trimestre';
    if ([7, 8, 9].includes(mois)) return '3ème trimestre';
    if ([10, 11, 12].includes(mois)) return '4ème trimestre';

    return 'Inconnu';
};

// Trimestre actuel (ex: "2ème trimestre")
const currentMonth = new Date().getMonth() + 1;
const trimestreLabel = computed(() => currentTrimestre(currentMonth));
  
</script>

<template>
    <Head title="Trimestre" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-primary-txt">
                Trimestres
            </h2>
        </template>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl">
                    <div class="bg-white shadow-md rounded-xl">
                        <div class="p-4 border-b bg-primary rounded-t-xl">
                            <h1 class="text-lg font-semibold text-white">Trimestre : {{ trimestreLabel }}</h1>
                        </div>
                        <div class="overflow-x-auto p-6">
                    <table class="min-w-full border">
                        <thead class="bg-primary-form text-primary-txt">
                            <tr class="h-10">
                                <th class="border border-primary-only px-4 py-2 text-left text-sm font-bold">N°</th>
                                <th class="whitespace-nowrap border border-primary-only px-4 py-2 text-left text-sm font-bold">Désignation</th>
                                <th class="border border-primary-only px-4 py-2 text-left text-sm font-bold">Stock Fin Précédent</th>
                                <th class="border border-primary-only px-4 py-2 text-left text-sm font-bold">Reçus</th>
                                <th class="border border-primary-only px-4 py-2 text-left text-sm font-bold">Traités</th>
                                <th class="border border-primary-only px-4 py-2 text-left text-sm font-bold">Retournés</th>
                                <th class="border border-primary-only px-4 py-2 text-left text-sm font-bold">Instances</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(designation, index) in designations" :key="index" 
                                class="border-t hover:bg-primary-form dark:hover:bg-primary-form">
                                <td class="px-4 py-2 text-left border border-b text-primary-txt text-sm">{{ index + 1 }}</td>
                                <td class="px-4 py-2 text-left border border-b text-primary-txt text-sm "> {{ designation }}</td>
                                <td class="px-4 py-2 border border-b text-primary-txt font-bold text-sm text-center">{{ props.stockFin[designation] ?? 0 }}</td>
                                <!-- 👇 On met la valeur trouvée ou 0 si absente -->
                                <td class="px-4 py-2 border border-b text-primary-txt font-bold text-sm text-center">{{ props.countsByTrimestre[designation] ?? 0 }}</td>                                
                                <td class="px-4 py-2 border border-b text-primary-txt font-bold text-sm text-center">{{ props.countsTraiterByTrimestre[designation] ?? 0 }}</td>
                                <td class="px-4 py-2 border border-b text-primary-txt font-bold text-sm text-center">{{ props.courriersRetourners[designation] ?? 0 }}</td>
                                <td class="px-4 py-2 border border-b text-primary-txt font-bold text-sm text-center">{{ props.instancesRestantes[designation] ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

 