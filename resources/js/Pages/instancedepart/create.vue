<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed  } from 'vue';
import { Head } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { router, usePage  } from '@inertiajs/vue3'
import { Link as InertiaLink } from '@inertiajs/vue3'
import BaseInput from '@/Components/BaseInput.vue' 
 
const props = defineProps({
    arrivee: Object,    
    arrivees: Array,
    departs: Array,
    depart: Object,
});
  
const numeroCD = ref('');  
const annee = ref('');
const refCourrierArrivee = ref('');

// Normalisation pour éviter les problèmes d'espaces et de casse
function normalize(str) {
    return str?.toString().trim().toLowerCase();
}
 
//  Filtrer par date et par Numéro + Tri croissant par txt_numdordrecd
const filtereDeparts = computed(() => {
    const filtered = props.departs.filter(depart => {
        const matchNumero = numeroCD.value
            ? normalize(depart.txt_numdordrecd)?.includes(normalize(numeroCD.value))
            : true;

        const matchAnnee = annee.value
            ? new Date(depart.dt_datecouriercd).getFullYear().toString() === annee.value
            : true;

        const matchArrivee = refCourrierArrivee.value
            ? normalize(depart.txt_destinatairecd)?.includes(normalize(refCourrierArrivee.value))
            : true;

        return matchNumero && matchAnnee && matchArrivee;
    });

    // Tri par ordre numérique de la partie avant "/"
    return filtered.sort((a, b) => {
        const numA = parseInt(a.txt_numdordrecd?.split("/")[0] || 0, 10);
        const numB = parseInt(b.txt_numdordrecd?.split("/")[0] || 0, 10);
        return numA - numB; // ordre croissant
    });
});
 
// Compter le nombre total de courrier
const totalCourrier = computed(() => {
    return props.departs.filter(totalarrivee => !!totalarrivee.txt_numdordrecd).length;
});
 
// filtrer les instances par categorie  
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};
  
// fonction d'envoi du formulaire
function supprimerCourrierDepart(depart) {
    const code = prompt("Entrez le code d'accès pour confirmer la suppression :");

    if (!code) {
        alert("Aucun code saisi. Suppression annulée.");
        return;
    }

    router.delete(route('instancedepart.destroy', depart.id), {
        data: { code_acces: code }, // ✅ on envoie le code au backend
        preserveScroll: true,
        onSuccess: () => {
            console.log('Suppression réussie');
        },
        onError: (errors) => {
            alert(errors.code_acces || "Erreur : code invalide ou suppression refusée.");
            console.error(errors);
        }
    });
}
 
</script>

<template>
    <Head title="Instances Départs">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl text-primary-txt font-semibold leading-tight"
            >
                Liste des Courriers Departs
            </h2>
            
            <!-- Vérification pour afficher un message si aucune donnée -->
            <template v-if="(!departs || departs.length === 0)">
                <p>Aucun Instance Départ n'est trouvé.</p>
            </template>
           
        </template v-else>

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
                                        <span v-if="totalCourrier>0" class="text-primary-txt">
                                            ({{ totalCourrier }})
                                        </span>
                                        <span v-else class="text-red-600">
                                            Aucun enregistrement
                                        </span> 
                                    </h1>  

                                    <form @submit.prevent class="flex flex-wrap items-center gap-2">
                                        <BaseInput
                                            class="w-24"
                                            label="Année"
                                            name="filtre-annee-depart"
                                            type="search"
                                            maxlength="4"
                                            placeholder="année"
                                            v-model="annee"
                                        />
                                        <BaseInput
                                            class="w-44"
                                            label="N° courrier"
                                            name="filtre-numero-depart"
                                            type="search"
                                            placeholder="Entrez numéro courrier"
                                            v-model="numeroCD"
                                        />
                                        <BaseInput
                                            class="w-40"
                                            label="Désignation"
                                            name="filtre-designation-depart"
                                            type="search"
                                            placeholder="Désignation"
                                            v-model="refCourrierArrivee"
                                        />
                                    </form>
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="max-h-[600px] overflow-x-auto shadow-md rounded-xl">
                                            <table class="table table-auto w-full text-base text-left text-primary-txt">

                                                <thead class="sticky top-0 z-10 text-primary-txt text-center border-l border-primary-only uppercase 
                                                        bg-primary-form text-primary-txt whitespace-nowrap">
                                                    <tr class="h-10">
                                                        <th scope="col" class="sticky left-0 z-30 px-6 py-3 border-l border-primary-only">N°</th>
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
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Fichier</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">ACTIONS</th>

                                                    </tr>
                                                </thead>
                                                        
                                                <tbody sortedDeparts>
                                                    <tr v-for="(depart, index) in filtereDeparts" :key="depart.id"  
                                                        class="h-20 bg-white text-primary-txt text-center font-bold whitespace-nowrap hover:bg-primary-form dark:hover:bg-primary-form">
                                                        <td scope="col" class="sticky left-0 z-0 border border-primary-only bg-white px-6 py-3 ">{{ index + 1 }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_bordereaucd || '-'}}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_numdordrecd  || '-'}}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_caracterecd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ formatDate(depart.dt_datecouriercd) || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_categoriecd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_referencecourierarriveecd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_nombrepiececd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_referencecd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_prenomcd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_nomcd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_numLotcd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_surfacecd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_situationcd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_nicadcd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_objetcd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_destinatairecd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ formatDate(depart.dt_dateenvoicd) || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_referencereceptioncd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_observationcd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ depart.txt_dureetraitementcd || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only"> 
                                                            <div v-if="depart.fichierPDFcd">
                                                                <a :href="`/storage/${depart.fichierPDFcd}`" target="_blank" class="text-blue-600 underline">
                                                                    Voir PDF
                                                                </a>
                                                            </div>
                                                            <div v-else class="italic text-primary-txt/60">Aucun fichier PDF</div>
                                                        </td>
                                                
                                                        <td class="flex items px-6 py-3">
                                                            <div class="mt-2">
                                                                <InertiaLink :href="`/depart/editdepart/${depart.id}`">
                                                                    <MazBtn 
                                                                        color="white" pastel size="sm"
                                                                        class="h-8 w-28 text-white bg-gradient-to-r from-green-400 via-green-500 
                                                                        to-green-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none 
                                                                        focus:ring-green-300 dark:focus:ring-green-800 shadow-lg shadow-green-500/50 
                                                                        dark:shadow-lg dark:shadow-green-800/80 font-medium rounded-lg text-sm px-5 
                                                                        py-2.5 text-center me-2 mb-2"
                                                                    >
                                                                        Modifier
                                                                    </MazBtn>
                                                                </InertiaLink> 
                                                            </div>
                                                            <div class="container">
                                                                <p></p>
                                                            </div>
                                                            <div class="mt-2">
                                                                <MazBtn 
                                                                    color="danger" size="sm"   
                                                                    @click="() => supprimerCourrierDepart(depart)"
                                                                    class="h-8 w-28 text-white bg-gradient-to-r from-danger-500 via-danger-600 
                                                                    to-danger-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none 
                                                                    focus:ring-danger-300 dark:focus:ring-danger-800 shadow-lg shadow-danger-500/50 
                                                                    dark:shadow-lg dark:shadow-danger-800/80 font-medium rounded-lg text-sm px-5 
                                                                    py-2.5 text-center me-2 mb-2"
                                                                >
                                                                    Supprimer
                                                                </MazBtn>
                                                            </div>
                                                        </td> 
                                                    </tr>
                                                </tbody>
                                            </table>
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
  padding-top: 0.25rem; /* équivaut à py-1 */
  padding-bottom: 0.25rem;
  line-height: 1rem; /* équivaut à leading-3 */
  font-size: 0.80rem; /* équivaut à text-xs */
}

</style>
 